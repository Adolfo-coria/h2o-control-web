<?php

namespace App\Http\Controllers;

use App\Models\Multa;
use App\Models\TarifaMulta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MultaController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $rolUsuario = strtolower($usuario->rol->nombre ?? ($usuario->rol->nombre_rol ?? ($usuario->rol->name ?? '')));
        
        $esGestion = str_contains($rolUsuario, 'admin') || 
                     str_contains($rolUsuario, 'super') || 
                     str_contains($rolUsuario, 'hacienda');

        $query = Multa::with('socio');

        if (!$esGestion) {
            $query->where('user_id', $usuario->id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar') && $esGestion) {
            $query->whereHas('socio', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->buscar . '%')
                  ->orWhere('email', 'like', '%' . $request->buscar . '%');
            });
        }

        $multas = $query->orderBy('fecha_multa', 'desc')->paginate(15);
        $tarifas = TarifaMulta::all();

        $totalPendiente = Multa::where('estado', 'pendiente')->sum('monto');
        $totalCobrado = Multa::where('estado', 'pagado')->sum('monto');
        $cantPendientes = Multa::where('estado', 'pendiente')->count();

        return view('multas.index', compact('multas', 'tarifas', 'totalPendiente', 'totalCobrado', 'cantPendientes', 'esGestion'));
    }

    public function create()
    {
        $socios = User::whereDoesntHave('rol', function ($query) {
            $query->whereRaw('LOWER(nombre) LIKE ?', ['%admin%'])
                  ->orWhereRaw('LOWER(nombre) LIKE ?', ['%super%'])
                  ->orWhereRaw('LOWER(nombre) LIKE ?', ['%hacienda%']);
        })
        ->whereNotIn('email', ['admin@otb.com', 'jonh@example.com', 'adolfo@example.com'])
        ->orderBy('name', 'asc')
        ->get();

        $tarifas = TarifaMulta::all();

        return view('multas.create', compact('socios', 'tarifas'));
    }

    public function store(Request $request)
    {
        $tipoRegistro = $request->input('tipo_registro', $request->has('socios') ? 'masivo' : 'individual');

        $request->validate([
            'tarifa_multa_id' => 'required',
            'monto'           => 'required|numeric|min:0',
            'fecha_multa'     => 'required|date',
            'motivo'          => 'nullable|string|max:255',
        ]);

        if ($request->tarifa_multa_id === 'otro') {
            $tarifaId = null;
            $nombreTarifa = $request->input('motivo', 'Otra Infracción');
        } else {
            $tarifa = TarifaMulta::find($request->tarifa_multa_id);
            $tarifaId = $tarifa ? $tarifa->id : null;
            $nombreTarifa = $request->input('tipo_multa_texto') 
                ?? ($tarifa ? $tarifa->nombre : ($request->motivo ?? 'Multa'));
        }

        if ($tipoRegistro === 'individual') {
            $request->validate(['user_id' => 'required|exists:users,id']);

            Multa::create([
                'user_id'         => $request->user_id,
                'tarifa_multa_id' => $tarifaId,
                'tipo_multa'      => $nombreTarifa,
                'monto'           => $request->monto,
                'motivo'          => $request->motivo,
                'fecha_multa'     => $request->fecha_multa,
                'estado'          => 'pendiente',
            ]);
        } else {
            $request->validate(['socios' => 'required|array|min:1']);

            foreach ($request->socios as $socioId) {
                Multa::create([
                    'user_id'         => $socioId,
                    'tarifa_multa_id' => $tarifaId,
                    'tipo_multa'      => $nombreTarifa,
                    'monto'           => $request->monto,
                    'motivo'          => $request->motivo,
                    'fecha_multa'     => $request->fecha_multa,
                    'estado'          => 'pendiente',
                ]);
            }
        }

        return redirect()->route('multas.index')->with('success', 'Multa(s) registrada(s) con éxito. ✅');
    }

    public function edit($id)
    {
        $multa = Multa::findOrFail($id);

        $socios = User::whereDoesntHave('rol', function ($query) {
            $query->whereRaw('LOWER(nombre) LIKE ?', ['%admin%'])
                  ->orWhereRaw('LOWER(nombre) LIKE ?', ['%super%'])
                  ->orWhereRaw('LOWER(nombre) LIKE ?', ['%hacienda%']);
        })
        ->whereNotIn('email', ['admin@otb.com', 'jonh@example.com', 'adolfo@example.com'])
        ->orderBy('name', 'asc')
        ->get();

        $tarifas = TarifaMulta::all();

        return view('multas.edit', compact('multa', 'socios', 'tarifas'));
    }

    public function update(Request $request, $id)
    {
        $multa = Multa::findOrFail($id);

        $request->validate([
            'user_id'         => 'required|exists:users,id',
            'tarifa_multa_id' => 'required',
            'monto'           => 'required|numeric|min:0',
            'fecha_multa'     => 'required|date',
            'estado'          => 'required|in:pendiente,pagado',
            'motivo'          => 'nullable|string|max:255',
        ]);

        if ($request->tarifa_multa_id === 'otro') {
            $tarifaId = null;
            $nombreTarifa = $request->input('motivo', 'Otra Infracción');
        } else {
            $tarifa = TarifaMulta::find($request->tarifa_multa_id);
            $tarifaId = $tarifa ? $tarifa->id : null;
            $nombreTarifa = $request->input('tipo_multa_texto') 
                ?? ($tarifa ? $tarifa->nombre : ($request->motivo ?? 'Multa'));
        }

        $multa->update([
            'user_id'         => $request->user_id,
            'tarifa_multa_id' => $tarifaId,
            'tipo_multa'      => $nombreTarifa,
            'monto'           => $request->monto,
            'motivo'          => $request->motivo,
            'fecha_multa'     => $request->fecha_multa,
            'estado'          => $request->estado,
        ]);

        return redirect()->route('multas.index')->with('success', 'Multa actualizada correctamente. ✏️');
    }

    public function destroy($id)
    {
        $multa = Multa::findOrFail($id);
        $multa->delete();

        return redirect()->route('multas.index')->with('success', 'La multa ha sido eliminada. 🗑️');
    }

    public function pagar(Request $request, $id)
    {
        $multa = Multa::findOrFail($id);

        if ($multa->estado === 'pagado') {
            return back()->with('error', 'La multa ya se encuentra pagada.');
        }

        $comprobantePath = null;
        if ($request->hasFile('comprobante')) {
            $comprobantePath = $request->file('comprobante')->store('comprobantes_multas', 'public');
        }

        DB::transaction(function () use ($multa, $comprobantePath) {
            $multa->update([
                'estado'           => 'pagado',
                'fecha_pago'       => now(),
                'comprobante_pago' => $comprobantePath,
            ]);

            if (class_exists('App\Models\Balance')) {
                \App\Models\Balance::create([
                    'mes'             => now()->format('F'),
                    'gestion'         => now()->year,
                    'ingresos'        => $multa->monto,
                    'egresos'         => 0,
                    'saldo_final'     => $multa->monto,
                    'detalle'         => "Cobro de Multa: {$multa->tipo_multa} - Socio: {$multa->socio->name}",
                    'comprobante_url' => $comprobantePath,
                ]);
            }
        });

        return redirect()->route('multas.index')->with('success', 'Pago de multa registrado con éxito. ✅');
    }

    public function exportar(Request $request)
    {
        $fileName = 'multas_otb_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-Type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $usuario = Auth::user();
        $rolUsuario = strtolower($usuario->rol->nombre ?? ($usuario->rol->nombre_rol ?? ($usuario->rol->name ?? '')));
        
        $esGestion = str_contains($rolUsuario, 'admin') || 
                     str_contains($rolUsuario, 'super') || 
                     str_contains($rolUsuario, 'hacienda');

        $query = Multa::with(['socio', 'tarifa']);

        if (!$esGestion) {
            $query->where('user_id', $usuario->id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar') && $esGestion) {
            $query->whereHas('socio', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->buscar . '%')
                  ->orWhere('email', 'like', '%' . $request->buscar . '%');
            });
        }

        $multas = $query->orderBy('fecha_multa', 'desc')->get();

        return response()->stream(function () use ($multas) {
            while (ob_get_level()) {
                ob_end_clean();
            }

            $file = fopen('php://output', 'w');

            // BOM UTF-8 para soporte de tildes y acentos en Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Indicador de delimitador para Excel
            fwrite($file, "sep=;\n");

            // Encabezados
            fputcsv($file, ['ID', 'Socio', 'Email', 'Tipo Infracción / Motivo', 'Monto (Bs.)', 'Fecha Multa', 'Estado'], ';');

            foreach ($multas as $m) {
                $tipoMotivo = $m->tipo_multa && $m->tipo_multa !== 'Multa' 
                    ? $m->tipo_multa 
                    : (optional($m->tarifa)->nombre ?? ($m->motivo ?? 'Multa'));

                fputcsv($file, [
                    $m->id,
                    $m->socio->name ?? 'Usuario Desconocido',
                    $m->socio->email ?? 'Sin Email',
                    $tipoMotivo,
                    number_format((float)$m->monto, 2, ',', ''),
                    \Carbon\Carbon::parse($m->fecha_multa)->format('d/m/Y'),
                    ucfirst($m->estado)
                ], ';');
            }

            fclose($file);
        }, 200, $headers);
    }
}