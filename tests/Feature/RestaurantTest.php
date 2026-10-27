<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Database\Seeders\RestaurantSeeder;
class RestaurantTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void {parent::setUp();$this->seed(RestaurantSeeder::class);}
 private function account($role='cliente'){return User::factory()->create(['rol'=>$role,'activo'=>true]);}
 public function test_public_menu_and_registration():void {
 $this->get('/')->assertOk();$this->getJson('/rest/api/public')->assertOk()->assertJsonCount(9,'products');
 $d=['name'=>'Cliente Prueba','email'=>'cliente@example.com','phone'=>'3001234567','password'=>'Prueba2026!'];
 $this->postJson('/rest/api/register',$d)->assertCreated();$this->postJson('/rest/api/register',$d)->assertUnprocessable();
 $this->postJson('/rest/api/login',['email'=>$d['email'],'password'=>$d['password']])->assertOk()->assertJsonPath('user.role','cliente')->assertJsonStructure(['csrf']);
 $this->getJson('/rest/api/me')->assertOk()->assertJsonStructure(['csrf']);
 $this->postJson('/rest/api/logout')->assertOk();$this->getJson('/rest/api/orders')->assertForbidden();
 }
 public function test_orders_total_privacy_and_status():void {
 $a=$this->account();$b=$this->account();$staff=$this->account('trabajador');$p=DB::table('products')->first();
 $id=$this->actingAs($a)->postJson('/rest/api/orders',['payment'=>'Efectivo','total'=>1,'items'=>[['id'=>$p->id,'qty'=>2]]])->assertCreated()->assertJsonPath('total',$p->price*2)->json('id');
 $this->actingAs($b)->getJson('/rest/api/orders')->assertJsonCount(0,'orders');
 $this->actingAs($a)->putJson('/rest/api/order-status',['id'=>$id,'status'=>'Entregado'])->assertForbidden();
 $this->actingAs($staff)->putJson('/rest/api/order-status',['id'=>$id,'status'=>'Entregado'])->assertUnprocessable();
 foreach(['En preparación','Listo','Entregado'] as $status)$this->putJson('/rest/api/order-status',compact('id','status'))->assertOk();
 $this->actingAs($a)->getJson('/rest/api/orders')->assertJsonPath('orders.0.status','Entregado');
 }
 public function test_reservation_capacity_and_isolation():void {
 DB::table('mesas')->where('numero','>',1)->update(['habilitada'=>false]);$a=$this->account();$b=$this->account();$slot=now('America/Bogota')->addDays(2)->format('Y-m-d').'T12:00';
 $this->actingAs($a)->postJson('/rest/api/reservations',['slot'=>$slot,'guests'=>2])->assertCreated();
 $this->actingAs($b)->postJson('/rest/api/reservations',['slot'=>$slot,'guests'=>2])->assertUnprocessable();
 $this->getJson('/rest/api/reservations')->assertJsonCount(0,'reservations');
 $this->postJson('/rest/api/reservations',['slot'=>'2020-01-01T12:00','guests'=>2])->assertUnprocessable();
 }
 public function test_roles_and_product_deactivation():void {
 $client=$this->account();$admin=$this->account('administrador');$p=(array)DB::table('products')->first();
 $this->actingAs($client)->getJson('/rest/api/users')->assertForbidden();$this->getJson('/rest/api/products')->assertForbidden();
 $p['active']=false;$this->actingAs($admin)->putJson('/rest/api/products',$p)->assertOk();
 $this->actingAs($client)->postJson('/rest/api/orders',['payment'=>'Efectivo','items'=>[['id'=>$p['id'],'qty'=>1]]])->assertUnprocessable();
 $this->actingAs($admin)->putJson('/rest/api/users',['id'=>$client->id,'name'=>$client->name,'email'=>$client->email,'phone'=>'3001234567','role'=>'cliente','active'=>false])->assertOk();
 $this->actingAs($client->fresh())->getJson('/rest/api/orders')->assertForbidden();
 }
 public function test_product_image_upload_preserve_replace_and_remove():void {
 $root=sys_get_temp_dir().'/bandeja-images-'.bin2hex(random_bytes(6));
 $this->app->usePublicPath($root);
 $admin=$this->account('administrador');$client=$this->account();
 $png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/l9sAAAAASUVORK5CYII=');
 $file=fn()=>\Illuminate\Http\UploadedFile::fake()->createWithContent('plato.png',$png);
 $d=['name'=>'Plato con foto','category'=>'Platos fuertes','description'=>'Arroz con ingredientes de la casa.','price'=>25000,'emoji'=>'🍛','active'=>1];
 try {
  $this->actingAs($client)->postJson('/rest/api/products',[...$d,'photo'=>$file()])->assertForbidden();
  $this->actingAs($admin)->postJson('/rest/api/products',[...$d,'photo'=>\Illuminate\Http\UploadedFile::fake()->createWithContent('mal.png','not an image')])->assertUnprocessable();
  $this->post('/rest/api/products',[...$d,'photo'=>$file()],['Accept'=>'application/json'])->assertOk();
  $p=DB::table('products')->where('name',$d['name'])->first();$old=$p->image_path;
  $this->assertFileExists($root.$old);
  $this->putJson('/rest/api/products',[...$d,'id'=>$p->id,'price'=>26000])->assertOk();
  $this->assertSame($old,DB::table('products')->where('id',$p->id)->value('image_path'));
  $this->post('/rest/api/products',[...$d,'id'=>$p->id,'photo'=>$file(),'_method'=>'PUT'],['Accept'=>'application/json'])->assertOk();
  $new=DB::table('products')->where('id',$p->id)->value('image_path');$this->assertNotSame($old,$new);$this->assertFileDoesNotExist($root.$old);$this->assertFileExists($root.$new);
  $this->putJson('/rest/api/products',[...$d,'id'=>$p->id,'remove_image'=>true])->assertOk();
  $this->assertNull(DB::table('products')->where('id',$p->id)->value('image_path'));$this->assertFileDoesNotExist($root.$new);
 } finally {if(is_dir($root))\Illuminate\Support\Facades\File::deleteDirectory($root);}
 }
}
