<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Mesa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        if ($request->user()->rol === 'cliente') {
            return view('clientes.profile', ['cliente' => $request->user()->cliente]);
        }

        return view('dashboard', [
            'totalClientes' => Cliente::count(), 'totalMesas' => Mesa::count(),
            'capacidad' => Mesa::where('habilitada', true)->sum('capacidad'),
            'totalUsuarios' => $request->user()->esAdministrador() ? User::where('activo', true)->count() : null,
            'clientes' => Cliente::latest('id')->limit(5)->get(),
            'mesas' => Mesa::orderBy('numero')->limit(8)->get(),
        ]);
    }
}
