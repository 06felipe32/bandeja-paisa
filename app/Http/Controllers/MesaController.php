<?php

namespace App\Http\Controllers;

use App\Http\Requests\MesaRequest;
use App\Models\Mesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MesaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:80'], 'estado' => ['nullable', 'in:habilitada,fuera_servicio'],
        ]);
        $mesas = Mesa::query()->when($filters['buscar'] ?? null, function ($q, $value): void {
            $q->where(fn ($q) => $q->where('numero', 'like', '%'.$value.'%')->orWhere('ubicacion', 'like', '%'.$value.'%'));
        })->when($filters['estado'] ?? null, fn ($q, $value) => $q->where('habilitada', $value === 'habilitada'))
            ->orderBy('numero')->paginate(12)->withQueryString();

        return view('mesas.index', compact('mesas', 'filters'));
    }

    public function create(): View
    {
        return view('mesas.form', ['mesa' => new Mesa(['habilitada' => true])]);
    }

    public function store(MesaRequest $request): RedirectResponse
    {
        $mesa = Mesa::create($request->validated());

        return redirect()->route('mesas.show', $mesa)->with('success', 'Mesa creada correctamente.');
    }

    public function show(Mesa $mesa): View
    {
        return view('mesas.show', compact('mesa'));
    }

    public function edit(Mesa $mesa): View
    {
        return view('mesas.form', compact('mesa'));
    }

    public function update(MesaRequest $request, Mesa $mesa): RedirectResponse
    {
        $this->protectReservations($mesa);
        $mesa->update($request->validated());

        return redirect()->route('mesas.show', $mesa)->with('success', 'Mesa actualizada correctamente.');
    }

    private function protectReservations(Mesa $mesa): void
    {
        $settings=json_decode(\Illuminate\Support\Facades\DB::table('restaurant_settings')->value('data'),true);
        if (\Illuminate\Support\Facades\DB::table('reservations')->where('mesa_id',$mesa->id)->where('slot','>',now()->subMinutes($settings['duration'] ?? 90)->format('Y-m-d\TH:i:s'))->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['mesa'=>'La mesa tiene reservas vigentes. Espera a que finalicen.']);
        }
    }

    public function destroy(Mesa $mesa): RedirectResponse
    {
        $this->protectReservations($mesa);
        if (\Illuminate\Support\Facades\DB::table('reservations')->where('mesa_id',$mesa->id)->exists()) {
            $mesa->update(['habilitada'=>false]);
        } else { $mesa->delete(); }

        return redirect()->route('mesas.index')->with('success', 'Mesa eliminada correctamente.');
    }
}
