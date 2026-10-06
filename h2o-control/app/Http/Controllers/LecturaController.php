<?php

namespace App\Http\Controllers;

use App\Models\Lectura;
use App\Models\User;
use Illuminate\Http\Request;

class LecturaController extends Controller
{
    // 1. Muestra la lista general de lecturas
    public function index()
    {
        $lecturas = Lectura::with('usuario')->orderBy('id', 'desc')->get();
        return view('lecturas.index', compact('lecturas'));
    }

    // 2. Formulario para registrar una lectura
    public function create()
    {
        $socios = User::all(); 
        return view('lecturas.create', compact('socios'));
    }

    // 2.1 API/AJAX: Obtiene la última lectura registrada de un socio
    public function obtenerUltimaLectura(Request $request, $usuario_id)
    {
        $ignoreId = $request->query('ignore_id');

        $ultimaLectura = Lectura::where('user_id', $usuario_id)
            ->when($ignoreId, function ($query) use ($ignoreId) {
                return $query->where('id', '!=', $ignoreId);
            })
            ->orderBy('id', 'desc')
            ->first();

        return response()->json([
            'tiene_lectura'  => (bool)$ultimaLectura,
            'lectura_actual' => $ultimaLectura ? $ultimaLectura->lectura_actual : 0
        ]);
    }

    // 3. Guarda la nueva lectura
    public function store(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'mes'               => 'required|string',
            'gestion'           => 'required|integer',
            'lectura_anterior'  => 'required|numeric|min:0',
            'lectura_actual'    => 'required|numeric|gte:lectura_anterior',
        ]);

        $consumo = $request->lectura_actual - $request->lectura_anterior;

        Lectura::create([
            'user_id'          => $request->user_id,
            'mes'               => $request->mes,
            'gestion'           => $request->gestion,
            'lectura_anterior'  => $request->lectura_anterior,
            'lectura_actual'    => $request->lectura_actual,
            'consumo'           => $consumo,
        ]);

        return redirect()->route('lecturas.index')->with('success', 'Lectura registrada ✓');
    }

    public function show(string $id) { }

    // 4. Formulario para editar
    public function edit(string $id)
    {
        $lectura = Lectura::findOrFail($id);
        $socios = User::all();
        return view('lecturas.edit', compact('lectura', 'socios'));
    }

    // 5. Actualiza el registro
    public function update(Request $request, string $id)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'mes'               => 'required|string',
            'gestion'           => 'required|integer',
            'lectura_anterior'  => 'required|numeric|min:0',
            'lectura_actual'    => 'required|numeric|gte:lectura_anterior',
        ]);

        $lectura = Lectura::findOrFail($id);
        $consumo = $request->lectura_actual - $request->lectura_anterior;

        $lectura->update([
            'user_id'          => $request->user_id,
            'mes'               => $request->mes,
            'gestion'           => $request->gestion,
            'lectura_anterior'  => $request->lectura_anterior,
            'lectura_actual'    => $request->lectura_actual,
            'consumo'           => $consumo,
        ]);

        return redirect()->route('lecturas.index')->with('success', 'Lectura actualizada ✓');
    }

    // 6. Eliminar registro
    public function destroy(string $id)
    {
        $lectura = Lectura::findOrFail($id);
        $lectura->delete();

        return redirect()->route('lecturas.index')->with('success', 'La lectura fue eliminada ✓');
    }

    // Vista exclusiva de lecturas del socio autenticado
    public function miConsumo()
    {
        $misLecturas = Lectura::where('user_id', auth()->id())
                              ->orderBy('id', 'desc')
                              ->get();

        return view('socio.consumo', compact('misLecturas'));
    }

    // Cambiar estado a pagado
    public function pagar(Lectura $lectura)
    {
        $lectura->update(['estado' => 'pagado']);

        return redirect()->back()->with('success', '¡Pago confirmado! La deuda ha sido saldada con éxito.');
    }

    // 7. Subida de código QR (Superadmin)
    public function actualizarQr(Request $request)
    {
        if (auth()->user()->rol_id != 1) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        $request->validate([
            'qr_code' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('qr_code')) {
            $request->file('qr_code')->storeAs('public', 'qr_oficial.png');
        }

        return back()->with('success', 'Código QR actualizado correctamente.');
    }

    // 8. Exportar lecturas a Excel (CSV limpio)
    public function exportar()
    {
        $fileName = 'lecturas_agua_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-Type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream(function () {
            while (ob_get_level()) {
                ob_end_clean();
            }

            $file = fopen('php://output', 'w');

            // Marca BOM UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Indicador de separador para Excel
            fwrite($file, "sep=;\n");

            // Encabezados de columna
            fputcsv($file, ['ID', 'Socio', 'Mes', 'Gestión', 'Lectura Anterior (m³)', 'Lectura Actual (m³)', 'Consumo (m³)', 'Monto (Bs.)', 'Estado'], ';');

            Lectura::with('usuario')->orderBy('id', 'desc')->chunk(200, function ($lecturas) use ($file) {
                foreach ($lecturas as $lectura) {
                    $consumo = (float)$lectura->lectura_actual - (float)$lectura->lectura_anterior;
                    $monto = $consumo * 2;

                    fputcsv($file, [
                        $lectura->id,
                        $lectura->usuario->name ?? 'Desconocido',
                        $lectura->mes,
                        $lectura->gestion,
                        $lectura->lectura_anterior,
                        $lectura->lectura_actual,
                        $consumo,
                        number_format($monto, 2, ',', ''),
                        ucfirst($lectura->estado ?? 'pendiente')
                    ], ';');
                }
            });

            fclose($file);
        }, 200, $headers);
    }
}