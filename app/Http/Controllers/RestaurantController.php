<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Hash};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
class RestaurantController extends Controller {
 const CATEGORIES=['Entradas','Platos fuertes','Sopas','Bebidas','Postres'];
 const STATES=['Recibido','En preparación','Listo','Entregado'];
 private function check($ok,$message){if(!$ok)throw ValidationException::withMessages(['data'=>$message]);}
 private function auth(Request $r,$roles=[]){$u=$r->user();abort_unless($u&&$u->activo,403,'Inicia sesión para continuar.');abort_if($roles&&!in_array($u->rol,$roles),403);return $u;}
 private function user($u){return $u?['id'=>$u->id,'name'=>$u->name,'email'=>$u->email,'phone'=>$u->phone??$u->cliente?->telefono??'','role'=>$u->rol==='trabajador'?'personal':$u->rol,'active'=>(int)$u->activo]:null;}
 private function settings(){return json_decode(DB::table('restaurant_settings')->value('data'),true);}
 public function index(){return view('restaurant');}
 public function handle(Request $r,string $path){
 $method=$r->method();if($r->has('email'))$r->merge(['email'=>strtolower(trim((string)$r->input('email')))]);$s=$this->settings();$now=now('America/Bogota');
 if($path==='public'&&$method==='GET')return response()->json(['products'=>DB::table('products')->where('active',true)->get(),'settings'=>$s,'categories'=>self::CATEGORIES]);
 if($path==='me'&&$method==='GET')return response()->json(['user'=>$r->user()?->activo?$this->user($r->user()):null,'csrf'=>csrf_token()])->header('Cache-Control','no-store');
 if($path==='register'&&$method==='POST'){
 $d=$r->validate(['name'=>'required|string|min:2|max:100','email'=>'required|email|max:150|unique:users,email','phone'=>'required|string|regex:/^[+0-9 ()-]{7,22}$/','password'=>['required','string','min:8',new \App\Rules\BcryptPasswordLength]]);$d['email']=strtolower($d['email']);$d['rol']='cliente';$d['activo']=true;$u=new User;$u->fill($d);$u->phone=$d['phone'];$u->save();return response()->json(['message'=>'Cuenta creada'],201);
 }
 if($path==='login'&&$method==='POST'){
 $r->validate(['email'=>'required|email','password'=>'required|string']);$key='restaurant-login:'.$r->ip();if(\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key,20))return response()->json(['error'=>'Demasiados intentos. Espera cinco minutos.'],429);\Illuminate\Support\Facades\RateLimiter::hit($key,300);if(!Auth::attempt(['email'=>strtolower($r->email),'password'=>$r->password,'activo'=>true]))return response()->json(['error'=>'Credenciales incorrectas o cuenta inactiva.'],401);\Illuminate\Support\Facades\RateLimiter::clear($key);$r->session()->regenerate();return response()->json(['user'=>$this->user($r->user()),'csrf'=>csrf_token()])->header('Cache-Control','no-store');
 }
 if($path==='logout'&&$method==='POST'){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return response()->json(['csrf'=>csrf_token()])->header('Cache-Control','no-store');}
 $u=$this->auth($r);
 if($path==='orders'){
 if($method==='GET'){$q=DB::table('orders')->join('users','users.id','=','orders.user_id')->select('orders.*','users.name as customer');if($u->rol==='cliente')$q->where('user_id',$u->id);$rows=$q->orderByDesc('created')->orderByDesc('orders.id')->get()->map(function($x){$x->items=json_decode($x->items);return $x;});return response()->json(['orders'=>$rows]);}
 if($method==='POST'){
 $this->auth($r,['cliente']);
 $d=$r->validate(['payment'=>['required',Rule::in(['Efectivo','Tarjeta de crédito/débito','Transferencia bancaria'])],'items'=>'required|array|min:1|max:100','items.*.id'=>'required|integer|distinct','items.*.qty'=>'required|integer|min:1|max:99']);
 return DB::transaction(function()use($d,$u,$now){$lines=[];$total=0;foreach($d['items'] as $item){$p=DB::table('products')->where('id',$item['id'])->where('active',true)->first();$this->check($p,'Un producto ya no está disponible.');$lines[]=['id'=>$p->id,'name'=>$p->name,'price'=>$p->price,'qty'=>$item['qty']];$total+=$p->price*$item['qty'];}$id='BP-'.Str::upper(Str::random(12));DB::table('orders')->insert(['id'=>$id,'user_id'=>$u->id,'created'=>$now->toIso8601String(),'status'=>self::STATES[0],'payment'=>$d['payment'],'total'=>$total,'items'=>json_encode($lines)]);return response()->json(['id'=>$id,'total'=>$total],201);});
 }}
 if($path==='order-status'&&$method==='PUT'){$this->auth($r,['administrador','trabajador']);$r->validate(['id'=>'required|string','status'=>['required',Rule::in(self::STATES)]]);$o=DB::table('orders')->where('id',$r->id)->first();$this->check($o,'Pedido inexistente.');$i=array_search($o->status,self::STATES);$this->check($i<3&&$r->status===self::STATES[$i+1],'Solo puedes avanzar a la siguiente etapa.');$count=DB::table('orders')->where('id',$o->id)->where('status',$o->status)->update(['status'=>$r->status]);$this->check($count===1,'El pedido cambió; actualiza la pantalla.');return response()->json([]);}
 if($path==='reservations'){
 if($method==='GET'){$q=DB::table('reservations')->join('users','users.id','=','reservations.user_id')->join('mesas','mesas.id','=','reservations.mesa_id')->select('reservations.*','users.name as customer','mesas.numero as table_no');if($u->rol==='cliente')$q->where('user_id',$u->id);$rows=$q->orderByDesc('slot')->get()->map(function($x)use($s,$now){if(Carbon::parse($x->slot,'America/Bogota')->addMinutes($s['duration'])->lt($now))$x->status='Finalizada';return $x;});return response()->json(['reservations'=>$rows]);}
 if($method==='POST'){$this->auth($r,['cliente']);$d=$r->validate(['slot'=>'required|date_format:Y-m-d\TH:i','guests'=>'required|integer|min:1|max:100']);return DB::transaction(function()use($d,$u,$now){
 // A write lock serializes availability decisions on SQLite, including simultaneous requests.
 DB::table('restaurant_settings')->where('id',1)->update(['id'=>1]);$s=$this->settings();$dt=Carbon::createFromFormat('!Y-m-d\TH:i',$d['slot'],'America/Bogota')->second(0);$end=$dt->copy()->addMinutes($s['duration']);$this->check($dt->gt($now),'La fecha debe ser futura.');$this->check($dt->minute%15===0,'Selecciona intervalos de 15 minutos.');$this->check($dt->format('H:i')>=$s['opening']&&$end->isSameDay($dt)&&$end->format('H:i')<=$s['closing'],'Reserva fuera del horario.');$occupied=DB::table('reservations')->where('status','Confirmada')->get()->filter(fn($x)=>Carbon::parse($x->slot)->lt($end)&&Carbon::parse($x->slot)->addMinutes($s['duration'])->gt($dt))->pluck('mesa_id');$table=DB::table('mesas')->where('habilitada',true)->where('capacidad','>=',$d['guests'])->whereNotIn('id',$occupied)->orderBy('capacidad')->orderBy('numero')->first();$this->check($table,'No hay mesa disponible para esa hora y cantidad de personas.');$id='RES-'.Str::upper(Str::random(12));DB::table('reservations')->insert(['id'=>$id,'user_id'=>$u->id,'mesa_id'=>$table->id,'slot'=>$dt->format('Y-m-d\TH:i:s'),'guests'=>$d['guests'],'status'=>'Confirmada','created'=>$now->toIso8601String()]);return response()->json(['id'=>$id,'table'=>$table->numero],201);});}
 }
 if($path==='products'){
  $this->auth($r,['administrador']);
  if($method==='GET')return response()->json(['products'=>DB::table('products')->get()]);
  if(in_array($method,['POST','PUT'])){
   $d=$r->validate([
    'name'=>'required|string|min:2|max:100',
    'category'=>['required',Rule::in(self::CATEGORIES)],
    'description'=>'required|string|min:5|max:1000',
    'price'=>'required|integer|min:1|max:10000000',
    'emoji'=>'required|string|max:10','active'=>'required|boolean',
    'photo'=>'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=4000,max_height=4000',
    'remove_image'=>'sometimes|boolean'
   ]);
   $current=null;
   if($method==='PUT'){$r->validate(['id'=>'required|integer|exists:products,id']);$current=DB::table('products')->where('id',$r->id)->first();}
   unset($d['photo'],$d['remove_image']);
   $old=$current->image_path ?? null;
   $new=null;
   if($r->hasFile('photo')){
    $file=$r->file('photo');
    $extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $ext=$extensions[$file->getMimeType()] ?? null;
    $this->check($ext,'Solo se permiten fotos JPG, PNG y WebP.');
    $dir=public_path('images/products');
    if(!is_dir($dir))mkdir($dir,0755,true);
    $name=Str::uuid()->toString().'.'.$ext;
    $file->move($dir,$name);
    $new='/images/products/'.$name;
    $d['image_path']=$new;
   }elseif($r->boolean('remove_image')){$d['image_path']=null;}
   try{
    if($method==='POST')DB::table('products')->insert($d);
    else DB::table('products')->where('id',$r->id)->update($d);
   }catch(\Throwable $e){if($new)is_file(public_path(ltrim($new,'/')))&&unlink(public_path(ltrim($new,'/')));throw $e;}
   // Delete only generated image paths after the database update succeeds.
   if(array_key_exists('image_path',$d)&&$old&&$old!==$d['image_path']&&preg_match('~^/images/products/[a-f0-9-]+\.(jpg|png|webp)$~',$old)){
    $previous=public_path(ltrim($old,'/'));if(is_file($previous))unlink($previous);
   }
   return response()->json([]);
  }
 }

 if($path==='users'){$this->auth($r,['administrador']);if($method==='GET')return response()->json(['users'=>User::orderBy('id')->get()->map(fn($x)=>$this->user($x))]);if($method==='PUT'){$d=$r->validate(['id'=>'required|integer|exists:users,id','name'=>'required|string|min:2|max:100','email'=>['required','email','max:150',Rule::unique('users','email')->ignore($r->id)],'phone'=>'required|string|regex:/^[+0-9 ()-]{7,22}$/','role'=>['required',Rule::in(['cliente','personal','administrador'])],'active'=>'required|boolean']);$this->check($u->id!=$d['id']||($d['role']==='administrador'&&$d['active']),'No puedes desactivarte ni quitarte el rol.');$target=User::findOrFail($d['id']);$target->fill(['name'=>$d['name'],'email'=>strtolower($d['email']),'rol'=>$d['role']==='personal'?'trabajador':$d['role'],'activo'=>$d['active']]);$target->phone=$d['phone'];$target->save();return response()->json([]);}}
 if($path==='settings'&&$method==='PUT'){$this->auth($r,['administrador']);$d=$r->validate(['address'=>'required|string|max:200','phone'=>'required|string|max:22','email'=>'required|email|max:150','lat'=>'required|numeric|between:-90,90','lon'=>'required|numeric|between:-180,180','opening'=>'required|date_format:H:i','closing'=>'required|date_format:H:i|after:opening','tables'=>'required|integer|min:1|max:100','seats'=>'required|integer|min:1|max:100','duration'=>'required|integer|min:15|max:300']);return DB::transaction(function()use($d,$now){DB::table('restaurant_settings')->where('id',1)->update(['id'=>1]);$s=$this->settings();$change=false;foreach(['opening','closing','tables','seats','duration'] as $k)if($s[$k]!=$d[$k])$change=true;if($change){$this->check(!DB::table('reservations')->where('slot','>',$now->copy()->subMinutes($s['duration'])->format('Y-m-d\TH:i:s'))->where('status','Confirmada')->exists(),'Hay reservas vigentes. Ajusta la capacidad cuando finalicen.');DB::table('mesas')->update(['habilitada'=>false]);for($i=1;$i<=$d['tables'];$i++)DB::table('mesas')->updateOrInsert(['numero'=>$i],['capacidad'=>$d['seats'],'ubicacion'=>'Salón principal','habilitada'=>true,'updated_at'=>now(),'created_at'=>now()]);}DB::table('restaurant_settings')->where('id',1)->update(['data'=>json_encode($d)]);return response()->json([]);});}
 abort(404);
 }
}
