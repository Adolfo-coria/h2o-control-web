<?php

namespace App\Http\Controllers;

use App\Models\TarifaMulta;
use Illuminate\Http\Request;

class TarifaMultaController extends Controller
{
    public function index()
    {
        $tarifas = TarifaMulta::all();
        return view('tarifas_multas.index', compact('tarifas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'               => 'required|string|max:255',
            'monto_predeterminado' => 'required|numeric|min:0',
        ]);

        TarifaMulta::create([
            'nombre'               => $request->nombre,
            'monto_predeterminado' => $request->monto_predeterminado,
            'descripcion'          => $request->descripcion ?? null,
        ]);

        return redirect()->route('tarifas-multas.index')
            ->with('success', '¡Tipo de multa creada correctamente!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'               => 'required|string|max:255',
            'monto_predeterminado' => 'required|numeric|min:0',
        ]);

        $tarifa = TarifaMulta::findOrFail($id);
        $tarifa->update([
            'nombre'               => $request->nombre,
            'monto_predeterminado' => $request->monto_predeterminado,
            'descripcion'          => $request->descripcion ?? null,
        ]);

        return redirect()->route('tarifas-multas.index')
            ->with('success', '¡Tarifa/Multa actualizada correctamente!');
    }

    public function destroy($id)
    {
        $tarifa = TarifaMulta::findOrFail($id);
        $tarifa->delete();

        return redirect()->route('tarifas-multas.index')
            ->with('success', 'Tarifa eliminada con éxito.');
    }
}