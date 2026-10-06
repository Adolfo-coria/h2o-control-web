<?php

namespace App\Http\Controllers;

use App\Models\BalanceMensual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BalanceMensualController extends Controller
{
    public function index(Request $request)
    {
        // Capturamos los filtros enviados desde la vista
        $gestion = $request->get('gestion');
        $mes = $request->get('mes');

        // Consultamos la base de datos aplicando los filtros dinámicos
        $balances = BalanceMensual::when($gestion, fn($q) => $q->where('gestion', $gestion))
            ->when($mes, fn($q) => $q->where('mes', $mes))
            ->orderBy('gestion', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Calculamos el balance total acumulado en caja
        $cajaActual = BalanceMensual::sum('ingresos') - BalanceMensual::sum('egresos');
        
        // Calculamos métricas para el año/gestión seleccionada o actual
        $gestionActual = $gestion ?? date('Y');
        $ingresosAnio = BalanceMensual::where('gestion', $gestionActual)->sum('ingresos');
        $egresosAnio = BalanceMensual::where('gestion', $gestionActual)->sum('egresos');

        return view('finanzas.index', compact(
            'balances', 
            'cajaActual', 
            'ingresosAnio', 
            'egresosAnio', 
            'gestionActual'
        ));
    }

    public function create()
    {
        return view('finanzas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'mes' => 'required|string',
            'gestion' => 'required|integer',
            'ingresos' => 'required|numeric|min:0',
            'egresos' => 'required|numeric|min:0',
            'comprobante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Máximo 5MB
        ]);

        $path = null;
        if ($request->hasFile('comprobante')) {
            $path = $request->file('comprobante')->store('comprobantes', 'public');
        }

        $saldo_final = $request->ingresos - $request->egresos;

        BalanceMensual::create([
            'mes' => $request->mes,
            'gestion' => $request->gestion,
            'ingresos' => $request->ingresos,
            'egresos' => $request->egresos,
            'saldo_final' => $saldo_final,
            'detalle' => $request->detalle,
            'comprobante_url' => $path,
        ]);

        return redirect()->route('finanzas.index')->with('success', 'Balance registrado ✓');
    }

    public function edit($id)
    {
        $balance = BalanceMensual::findOrFail($id);
        return view('finanzas.edit', compact('balance'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'mes' => 'required|string',
            'gestion' => 'required|integer',
            'ingresos' => 'required|numeric|min:0',
            'egresos' => 'required|numeric|min:0',
            'comprobante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $balance = BalanceMensual::findOrFail($id);
        
        // Si sube un archivo nuevo, eliminamos el anterior del almacenamiento local
        if ($request->hasFile('comprobante')) {
            if ($balance->comprobante_url) {
                Storage::disk('public')->delete($balance->comprobante_url);
            }
            $path = $request->file('comprobante')->store('comprobantes', 'public');
            $balance->comprobante_url = $path;
        }

        $saldo_final = $request->ingresos - $request->egresos;

        $balance->update([
            'mes' => $request->mes,
            'gestion' => $request->gestion,
            'ingresos' => $request->ingresos,
            'egresos' => $request->egresos,
            'saldo_final' => $saldo_final,
            'detalle' => $request->detalle,
        ]);

        return redirect()->route('finanzas.index')->with('success', 'Balance actualizado ✓');
    }

    public function destroy($id)
    {
        $balance = BalanceMensual::findOrFail($id);
        
        if ($balance->comprobante_url) {
            Storage::disk('public')->delete($balance->comprobante_url);
        }
        
        $balance->delete();

        return redirect()->route('finanzas.index')->with('success', 'Balance eliminado ✓');
    }

    public function exportar(Request $request)
    {
        $fileName = 'finanzas_otb_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-Type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Aplicar los mismos filtros recibidos de la vista
        $gestion = $request->get('gestion');
        $mes = $request->get('mes');

        $query = BalanceMensual::query();

        if ($gestion) {
            $query->where('gestion', $gestion);
        }

        if ($mes) {
            $query->where('mes', $mes);
        }

        $balances = $query->orderBy('gestion', 'desc')->orderBy('id', 'desc')->get();

        return response()->stream(function () use ($balances) {
            while (ob_get_level()) {
                ob_end_clean();
            }

            $file = fopen('php://output', 'w');

            // BOM UTF-8 para que Excel abra correctamente tildes y caracteres especiales
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Indicador para que Excel use punto y coma (;) como separador de columnas
            fwrite($file, "sep=;\n");

            // Encabezados del archivo
            fputcsv($file, ['ID', 'Mes', 'Gestión', 'Ingresos (Bs.)', 'Egresos (Bs.)', 'Saldo Final (Bs.)', 'Detalles'], ';');

            foreach ($balances as $b) {
                fputcsv($file, [
                    $b->id,
                    $b->mes,
                    $b->gestion,
                    number_format((float)$b->ingresos, 2, ',', ''),
                    number_format((float)$b->egresos, 2, ',', ''),
                    number_format((float)$b->saldo_final, 2, ',', ''),
                    $b->detalle ?? 'Sin observaciones'
                ], ';');
            }

            fclose($file);
        }, 200, $headers);
    }
}