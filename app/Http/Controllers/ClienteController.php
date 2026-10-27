<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['buscar' => ['nullable', 'string', 'max:100']]);
        $buscar = $filters['buscar'] ?? '';
        $clientes = Cliente::query()->when($buscar !== '', function ($query) use ($buscar): void {
            $query->where(function ($q) use ($buscar): void {
                foreach (['nombre', 'apellido', 'numero_documento', 'email', 'telefono'] as $field) {
                    $q->orWhere($field, 'like', '%'.$buscar.'%');
                }
            });
        })->orderBy('apellido')->orderBy('nombre')->orderBy('id')->paginate(10)->withQueryString();

        return view('clientes.index', compact('clientes', 'buscar'));
    }

    public function create(): View
    {
        return view('clientes.create');
    }

    public function store(ClienteRequest $request): RedirectResponse
    {
        $cliente = Cliente::create($request->validated());

        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente registrado correctamente.');
    }

    public function show(Cliente $cliente): View
    {
        return view('clientes.show', compact('cliente'));
    }

    public function profile(Request $request): View
    {
        return view('clientes.profile', ['cliente' => $request->user()->cliente]);
    }
}
