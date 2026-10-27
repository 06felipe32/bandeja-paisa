<!doctype html>
<html lang="es">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
 <title>@yield('title', 'Inicio') · La Bandeja Paisa</title>
 <meta name="color-scheme" content="light">
 <link rel="icon" type="image/svg+xml" href="{{ asset('marca.svg') }}">
 <link rel="stylesheet" href="{{ asset('css/app.css') }}">
 <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
<a class="skip" href="#contenido">Saltar al contenido</a>
<div class="app-shell">
 <aside class="sidebar">
  <a class="brand" href="{{ route('dashboard') }}"><span class="brand-symbol">ep<span>✦</span></span><span>LA EMBAJADA<small>PAISA · RESTAURANTE</small></span></a>
  <div class="nav-caption">TU RESTAURANTE, EN ORDEN</div>
  <nav class="main-nav" aria-label="Menú principal">
   <a href="{{ route('restaurante') }}" aria-label="Volver al menú principal del restaurante"><x-icon name="home"/> Volver al menú</a>
   <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><x-icon name="home"/> Inicio</a>
   @if(auth()->user()->rol !== 'cliente')
   <a class="{{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}"><x-icon name="users"/> Clientes</a>
   <a class="{{ request()->routeIs('mesas.*') ? 'active' : '' }}" href="{{ route('mesas.index') }}"><x-icon name="table"/> Mesas</a>
   @else
   <a class="{{ request()->routeIs('perfil') ? 'active' : '' }}" href="{{ route('perfil') }}"><x-icon name="users"/> Mi perfil</a>
   @endif
   @if(auth()->user()->esAdministrador())<a class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}"><x-icon name="shield"/> Usuarios y roles</a>@endif
  </nav>
  <div class="sidebar-note"><span class="small-star">✦</span><p>El sabor de casa.<br>La tranquilidad de tener<br>todo en su lugar.</p><small>Pasto, Nariño · Colombia</small></div>
  <div class="sidebar-bottom"><span class="status-dot"></span> Gestión del restaurante</div>
 </aside>
 <div class="workspace">
  <header class="topbar"><div class="breadcrumb"><a href="{{ route('restaurante') }}">Restaurante</a> <span>/</span> <strong>@yield('title', 'Inicio')</strong></div>
   <div class="account"><span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span>{{ auth()->user()->name }}<small>{{ auth()->user()->nombreRol() }}</small></span><form method="post" action="{{ route('logout') }}">@csrf<button class="icon-button" title="Cerrar sesión" aria-label="Cerrar sesión"><x-icon name="logout"/></button></form></div>
  </header>
  <main id="contenido" class="main"><x-alerts/>@yield('content')</main>
  <footer class="footer"><span>La Bandeja Paisa</span><span>Hecho para recibir mejor.</span></footer>
 </div>
</div>
</body></html>
