<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Multa;
use App\Models\Lectura;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $esAdmin = in_array($user->rol_id, [1, 2]) || 
                   in_array($user->email, ['jonh@example.com', 'adolfo@example.com']);

        if (!$esAdmin) {
            abort(403, 'No tienes permisos para acceder a este módulo.');
        }

        // 1. Filtros
        $mesSel = $request->get('mes', ''); // '' = Todos los meses
        $gestion = $request->get('gestion', date('Y'));

        // Cargar registros completos
        $todasLecturas = Lectura::all();
        $todasMultas = Multa::all();

        // Helper para extraer el monto de la lectura
        $extraerMontoLectura = function ($lectura) {
            if (isset($lectura->monto) && (float)$lectura->monto > 0) return (float) $lectura->monto;
            if (isset($lectura->monto_total) && (float)$lectura->monto_total > 0) return (float) $lectura->monto_total;
            if (isset($lectura->total) && (float)$lectura->total > 0) return (float) $lectura->total;
            if (isset($lectura->consumo) && (float)$lectura->consumo > 0) return (float) ($lectura->consumo * 2);
            return 100.0;
        };

        // Helper para obtener el número de mes (1-12)
        $obtenerNumMes = function ($item, $campoFecha = 'fecha_lectura') {
            if (isset($item->mes_num)) return (int) $item->mes_num;
            
            $textoMes = $item->mes_gestion ?? $item->mes ?? null;
            if ($textoMes) {
                $t = strtolower(trim($textoMes));
                $mesesMap = [
                    'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
                    'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
                    'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12
                ];
                foreach ($mesesMap as $nombre => $num) {
                    if (str_contains($t, $nombre)) return $num;
                }
            }

            $fecha = $item->$campoFecha ?? $item->created_at ?? null;
            if ($fecha) {
                try { return (int) Carbon::parse($fecha)->format('m'); } catch (\Exception $e) {}
            }
            return null;
        };

        // 2. Registros filtrados por Mes/Año para Tabla y KPIs
        $lecturasFiltradas = $todasLecturas->filter(function ($l) use ($mesSel, $obtenerNumMes) {
            if (!empty($mesSel)) {
                return $obtenerNumMes($l) == (int) $mesSel;
            }
            return true;
        });

        $multasFiltradas = $todasMultas->filter(function ($m) use ($mesSel, $obtenerNumMes) {
            if (!empty($mesSel)) {
                return $obtenerNumMes($m, 'fecha_multa') == (int) $mesSel;
            }
            return true;
        });

        // Totales KPI
        $totalAguaCobrada = 0;
        $totalAguaPendiente = 0;

        foreach ($lecturasFiltradas as $l) {
            $est = strtolower(trim($l->estado ?? ''));
            $monto = $extraerMontoLectura($l);

            if ($est === 'pagado') {
                $totalAguaCobrada += $monto;
            } else {
                $totalAguaPendiente += $monto;
            }
        }

        $totalMultasCabradas = 0;
        $totalMultasPendientes = 0;

        foreach ($multasFiltradas as $m) {
            $estM = strtolower(trim($m->estado ?? ''));
            $montoM = (float) ($m->monto ?? 0);

            if ($estM === 'pagado') {
                $totalMultasCabradas += $montoM;
            } else {
                $totalMultasPendientes += $montoM;
            }
        }

        $totalGeneralIngresado = $totalAguaCobrada + $totalMultasCabradas;
        $totalGeneralPorCobrar = $totalAguaPendiente + $totalMultasPendientes;

        // 3. MAPPING DE DEUDORES PARA LA TABLA
        $socios = User::all();
        $sociosMorosos = collect();

        foreach ($socios as $socio) {
            $lecturasP = $lecturasFiltradas->filter(function ($l) use ($socio) {
                $socioId = $l->user_id ?? $l->socio_id ?? $l->usuario_id ?? null;
                return (string) $socioId === (string) $socio->id && 
                       strtolower(trim($l->estado ?? '')) === 'pendiente';
            });

            $multasP = $multasFiltradas->filter(function ($m) use ($socio) {
                $socioId = $m->socio_id ?? $m->usuario_id ?? $m->user_id ?? null;
                return (string) $socioId === (string) $socio->id && 
                       strtolower(trim($m->estado ?? '')) === 'pendiente';
            });

            $deudaAgua = 0;
            foreach ($lecturasP as $lp) {
                $deudaAgua += $extraerMontoLectura($lp);
            }

            $deudaMultas = 0;
            foreach ($multasP as $mp) {
                $deudaMultas += (float) ($mp->monto ?? 0);
            }

            $deudaTotal = $deudaAgua + $deudaMultas;
            $cantMeses = $lecturasP->count();

            if ($deudaTotal > 0 || $cantMeses > 0 || $multasP->count() > 0) {
                $socioCopy = clone $socio;
                $socioCopy->cant_meses_mora = max($cantMeses, 1);
                $socioCopy->cant_multas_mora = $multasP->count();
                $socioCopy->deuda_agua = $deudaAgua;
                $socioCopy->deuda_multas = $deudaMultas;
                $socioCopy->deuda_total = $deudaTotal;

                $sociosMorosos->push($socioCopy);
            }
        }

        $sociosMorosos = $sociosMorosos->sortByDesc('deuda_total')->values();

        // 4. DATOS PARA EL GRÁFICO (RESPONDE AL FILTRO DEL MES)
        $mesesNombresCompletos = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $mesesGrafico = [];
        $datosAguaCobrada = [];
        $datosMultasCobradas = [];
        $datosPendientes = [];

        if (!empty($mesSel)) {
            $numM = (int) $mesSel;
            $mesesGrafico = [$mesesNombresCompletos[$numM - 1]];

            $aCobrada = 0; $aPendiente = 0;
            foreach ($todasLecturas as $l) {
                if ($obtenerNumMes($l) == $numM) {
                    $est = strtolower(trim($l->estado ?? ''));
                    if ($est === 'pagado') $aCobrada += $extraerMontoLectura($l);
                    else $aPendiente += $extraerMontoLectura($l);
                }
            }

            $mCobrada = 0; $mPendiente = 0;
            foreach ($todasMultas as $m) {
                if ($obtenerNumMes($m, 'fecha_multa') == $numM) {
                    $estM = strtolower(trim($m->estado ?? ''));
                    if ($estM === 'pagado') $mCobrada += (float) ($m->monto ?? 0);
                    else $mPendiente += (float) ($m->monto ?? 0);
                }
            }

            $datosAguaCobrada = [$aCobrada];
            $datosMultasCobradas = [$mCobrada];
            $datosPendientes = [$aPendiente + $mPendiente];
        } else {
            $mesesGrafico = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

            for ($m = 1; $m <= 12; $m++) {
                $aCobrada = 0; $aPendiente = 0;
                foreach ($todasLecturas as $l) {
                    if ($obtenerNumMes($l) == $m) {
                        $est = strtolower(trim($l->estado ?? ''));
                        if ($est === 'pagado') $aCobrada += $extraerMontoLectura($l);
                        else $aPendiente += $extraerMontoLectura($l);
                    }
                }

                $mCobrada = 0; $mPendiente = 0;
                foreach ($todasMultas as $multa) {
                    if ($obtenerNumMes($multa, 'fecha_multa') == $m) {
                        $estM = strtolower(trim($multa->estado ?? ''));
                        if ($estM === 'pagado') $mCobrada += (float) ($multa->monto ?? 0);
                        else $mPendiente += (float) ($multa->monto ?? 0);
                    }
                }

                $datosAguaCobrada[] = $aCobrada;
                $datosMultasCobradas[] = $mCobrada;
                $datosPendientes[] = $aPendiente + $mPendiente;
            }
        }

        return view('reportes.index', compact(
            'mesSel',
            'gestion',
            'totalAguaCobrada',
            'totalAguaPendiente',
            'totalMultasCabradas',
            'totalMultasPendientes',
            'totalGeneralIngresado',
            'totalGeneralPorCobrar',
            'sociosMorosos',
            'mesesGrafico',
            'datosAguaCobrada',
            'datosMultasCobradas',
            'datosPendientes'
        ));
    }

    public function exportar(Request $request)
    {
        $user = auth()->user();
        $esAdmin = in_array($user->rol_id, [1, 2]) || 
                   in_array($user->email, ['jonh@example.com', 'adolfo@example.com']);

        if (!$esAdmin) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        // Capturar los mismos filtros que en la vista principal
        $mesSel = $request->get('mes', '');
        $gestion = $request->get('gestion', date('Y'));

        $todasLecturas = Lectura::all();
        $todasMultas = Multa::all();

        $extraerMontoLectura = function ($lectura) {
            if (isset($lectura->monto) && (float)$lectura->monto > 0) return (float) $lectura->monto;
            if (isset($lectura->monto_total) && (float)$lectura->monto_total > 0) return (float) $lectura->monto_total;
            if (isset($lectura->total) && (float)$lectura->total > 0) return (float) $lectura->total;
            if (isset($lectura->consumo) && (float)$lectura->consumo > 0) return (float) ($lectura->consumo * 2);
            return 100.0;
        };

        $obtenerNumMes = function ($item, $campoFecha = 'fecha_lectura') {
            if (isset($item->mes_num)) return (int) $item->mes_num;
            $textoMes = $item->mes_gestion ?? $item->mes ?? null;
            if ($textoMes) {
                $t = strtolower(trim($textoMes));
                $mesesMap = [
                    'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
                    'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
                    'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12
                ];
                foreach ($mesesMap as $nombre => $num) {
                    if (str_contains($t, $nombre)) return $num;
                }
            }
            $fecha = $item->$campoFecha ?? $item->created_at ?? null;
            if ($fecha) {
                try { return (int) Carbon::parse($fecha)->format('m'); } catch (\Exception $e) {}
            }
            return null;
        };

        // Filtrado exacto de lecturas y multas pendientes
        $lecturasFiltradas = $todasLecturas->filter(function ($l) use ($mesSel, $obtenerNumMes) {
            if (!empty($mesSel)) {
                return $obtenerNumMes($l) == (int) $mesSel;
            }
            return true;
        });

        $multasFiltradas = $todasMultas->filter(function ($m) use ($mesSel, $obtenerNumMes) {
            if (!empty($mesSel)) {
                return $obtenerNumMes($m, 'fecha_multa') == (int) $mesSel;
            }
            return true;
        });

        // Generar lista de socios morosos exactamente igual a la tabla
        $socios = User::all();
        $sociosMorosos = collect();

        foreach ($socios as $socio) {
            $lecturasP = $lecturasFiltradas->filter(function ($l) use ($socio) {
                $socioId = $l->user_id ?? $l->socio_id ?? $l->usuario_id ?? null;
                return (string) $socioId === (string) $socio->id && 
                       strtolower(trim($l->estado ?? '')) === 'pendiente';
            });

            $multasP = $multasFiltradas->filter(function ($m) use ($socio) {
                $socioId = $m->socio_id ?? $m->usuario_id ?? $m->user_id ?? null;
                return (string) $socioId === (string) $socio->id && 
                       strtolower(trim($m->estado ?? '')) === 'pendiente';
            });

            $deudaAgua = 0;
            foreach ($lecturasP as $lp) {
                $deudaAgua += $extraerMontoLectura($lp);
            }

            $deudaMultas = 0;
            foreach ($multasP as $mp) {
                $deudaMultas += (float) ($mp->monto ?? 0);
            }

            $deudaTotal = $deudaAgua + $deudaMultas;
            $cantMeses = $lecturasP->count();

            if ($deudaTotal > 0 || $cantMeses > 0 || $multasP->count() > 0) {
                $socioCopy = clone $socio;
                $socioCopy->cant_meses_mora = max($cantMeses, 1);
                $socioCopy->deuda_agua = $deudaAgua;
                $socioCopy->deuda_multas = $deudaMultas;
                $socioCopy->deuda_total = $deudaTotal;

                $sociosMorosos->push($socioCopy);
            }
        }

        $sociosMorosos = $sociosMorosos->sortByDesc('deuda_total')->values();

        $filename = "deudores_otb_" . ($mesSel ? "mes_{$mesSel}_" : "todos_") . "gestion_{$gestion}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($sociosMorosos) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 para compatibilidad Excel

            // Encabezados exactos de la tabla
            fputcsv($file, ['Socio / Afiliado', 'CI', 'Meses en Mora', 'Monto Agua (Bs.)', 'Monto Multas (Bs.)', 'Total Acumulado (Bs.)', 'Acción Recomendada'], ';');

            foreach ($sociosMorosos as $socio) {
                $accion = ($socio->cant_meses_mora >= 3) ? 'Corte de Agua' : 'Notificar';

                fputcsv($file, [
                    $socio->name,
                    $socio->ci ?? 'Sin CI',
                    $socio->cant_meses_mora . ($socio->cant_meses_mora == 1 ? ' Mes' : ' Meses'),
                    number_format($socio->deuda_agua, 2, '.', ''),
                    number_format($socio->deuda_multas, 2, '.', ''),
                    number_format($socio->deuda_total, 2, '.', ''),
                    $accion
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}