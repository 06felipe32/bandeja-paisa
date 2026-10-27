<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Ingresar · La Bandeja Paisa</title><link rel="icon" href="{{ asset('marca.svg') }}"><link rel="stylesheet" href="{{ asset('css/app.css') }}"><script src="{{ asset('js/app.js') }}" defer></script></head>
<body class="login-body"><a class="skip" href="#ingreso">Saltar al formulario</a>
 <main class="login-shell">
  <section class="login-brand"><div class="brand"><span class="brand-symbol">ep<span>✦</span></span><span>LA EMBAJADA<small>PAISA · RESTAURANTE</small></span></div>
   <div class="login-story"><div class="eyebrow">BIENVENIDO A NUESTRA MESA</div><h1>El buen servicio<br>empieza con<br><em>todo en orden.</em></h1><p>Un solo lugar para cuidar de tus clientes,<br>tu equipo y cada mesa del restaurante.</p>
    <svg class="table-art" viewBox="0 0 440 170" role="img" aria-label="Ilustración de mesas del restaurante"><g fill="none" stroke="#b8cbb5" stroke-width="2"><rect x="65" y="32" width="120" height="100" rx="22"/><path d="M90 22v-9h65v9M90 142v9h65v-9M55 52H40v60h15M195 52h15v60h-15"/><circle cx="310" cy="82" r="50"/><path d="M290 20V8h40v12M290 144v12h40v-12M248 62h-12v40h12M372 62h12v40h-12"/></g><g fill="#d3b272"><circle cx="107" cy="82" r="16"/><circle cx="145" cy="82" r="16"/><circle cx="310" cy="82" r="16"/></g></svg>
   </div><small class="login-location">PASTO, NARIÑO <span>✦</span> EL SABOR DE CASA</small>
  </section>
  <section class="login-form-side" id="ingreso"><div class="login-form-wrap"><span class="label-pill"><x-icon name="lock" size="15"/> Acceso al sistema</span><h2>Qué gusto verte.</h2><p class="muted">Ingresa con tu cuenta para continuar.</p><x-alerts/>
   <form method="post" action="{{ route('login.store') }}" class="stack">@csrf
    <x-field name="email" label="Correo electrónico" type="email" :required="true" maxlength="150" autocomplete="username" placeholder="tu@correo.com" autofocus/>
    <div class="password-wrap"><x-field name="password" label="Contraseña" type="password" :required="true" maxlength="255" autocomplete="current-password"/><button type="button" class="password-toggle" data-password="password" aria-label="Mostrar contraseña" aria-pressed="false"><x-icon name="eye"/></button></div>
    <button class="button full">Ingresar <x-icon name="arrow" size="18"/></button>
   </form><p class="login-help">¿Necesitas una cuenta o recuperar tu acceso?<br>Comunícate con el administrador del restaurante.</p>
   <div class="login-fine"><x-icon name="shield" size="16"/> Tu acceso es personal. Cuida tus credenciales.</div>
  </div></section>
 </main>
</body></html>
