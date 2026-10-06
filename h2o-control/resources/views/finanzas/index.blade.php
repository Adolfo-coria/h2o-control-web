<script src="https://cdn.tailwindcss.com"></script>
<!-- CDN de ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<x-app-layout>
    <div class="py-12 bg-sky-200/70 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Encabezado Principal -->
            <div class="md:flex md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-extrabold text-3xl text-blue-950 leading-tight tracking-tight drop-shadow-sm">
                        Portal de Finanzas y Transparencia
                    </h2>
                    <p class="text-sm text-sky-900 mt-1 font-bold">Estado de cuentas y balances mensuales de la OTB</p>
                </div>
                
                @php
                    $rolUsuario = Auth::user()->rol->nombre ?? (Auth::user()->rol->nombre_rol ?? (Auth::user()->rol->name ?? ''));
                    $nombreRol = strtolower($rolUsuario);

                    // Roles diferenciados para finanzas
                    $esSuperAdmin = str_contains($nombreRol, 'super');
                    $esAdmin = $esSuperAdmin || str_contains($nombreRol, 'admin');
                @endphp

                @if($esAdmin)
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('finanzas.create') }}" class="inline-flex items-center justify-center bg-blue-700 hover:bg-blue-800 text-white px-6 py-3.5 rounded-xl font-extrabold shadow-lg shadow-blue-400/50 transition-all duration-200 ease-in-out transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Registrar Balance Mensual
                    </a>
                </div>
                @endif
            </div>

            @php
                $ordenMeses = [
                    'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
                    'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
                    'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12
                ];

                $balancesOrdenados = collect($balances ?? [])->sortBy(function($item) use ($ordenMeses) {
                    $numMes = $ordenMeses[strtolower(trim($item->mes))] ?? 99;
                    return ($item->gestion * 100) + $numMes;
                });

                // CÁLCULO DINÁMICO DE TARJETAS
                $gestionFiltro = request('gestion');
                $mesFiltro = request('mes');

                // Sumamos ingresos y egresos de la lista obtenida
                $totalIngresos = $balancesOrdenados->sum('ingresos');
                $totalEgresos = $balancesOrdenados->sum('egresos');

                // Formateo del título dinámico de las tarjetas
                if ($gestionFiltro && $mesFiltro) {
                    $etiquetaIngresos = "Ingresos ($mesFiltro $gestionFiltro)";
                    $etiquetaGastos = "Gastos ($mesFiltro $gestionFiltro)";
                } elseif ($gestionFiltro) {
                    $etiquetaIngresos = "Ingresos (Gestión $gestionFiltro)";
                    $etiquetaGastos = "Gastos (Gestión $gestionFiltro)";
                } elseif ($mesFiltro) {
                    $etiquetaIngresos = "Ingresos (Mes $mesFiltro - Todas las Gestiones)";
                    $etiquetaGastos = "Gastos (Mes $mesFiltro - Todas las Gestiones)";
                } else {
                    $etiquetaIngresos = "Ingresos Históricos";
                    $etiquetaGastos = "Gastos Históricos";
                }
            @endphp

            <!-- Widgets de Resumen -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Caja Actual -->
                <div class="bg-white rounded-2xl p-6 shadow-md border-l-8 border-blue-600 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-sky-600 uppercase tracking-wider">Caja Actual (Total)</p>
                        <h3 class="text-3xl font-black text-blue-950 mt-1">Bs. {{ number_format($cajaActual ?? 0, 2) }}</h3>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <!-- Ingresos -->
                <div class="bg-white rounded-2xl p-6 shadow-md border-l-8 border-green-500 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-sky-600 uppercase tracking-wider">{{ $etiquetaIngresos }}</p>
                        <h3 class="text-3xl font-black text-green-600 mt-1">Bs. {{ number_format($totalIngresos, 2) }}</h3>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full text-green-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                </div>

                <!-- Gastos -->
                <div class="bg-white rounded-2xl p-6 shadow-md border-l-8 border-red-500 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-sky-600 uppercase tracking-wider">{{ $etiquetaGastos }}</p>
                        <h3 class="text-3xl font-black text-red-600 mt-1">Bs. {{ number_format($totalEgresos, 2) }}</h3>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full text-red-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Gráfico ApexCharts con Scroll Adaptable -->
            <div class="bg-white p-5 rounded-2xl shadow-xl shadow-sky-900/10 border border-sky-100 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-md font-black text-blue-950 uppercase tracking-wider">Comparativo Financiero Mensual</h3>
                </div>

                <div class="w-full overflow-x-auto pb-2">
                    <div id="chartFinanzas" class="w-full"></div>
                </div>
            </div>

            <!-- Barra de Filtros y Botón de Excel -->
            <div class="bg-white p-4 rounded-2xl shadow-md border border-sky-100 flex flex-wrap items-center justify-between gap-4">
                <form id="formFiltros" method="GET" action="{{ route('finanzas.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Filtro Gestión -->
                    <div class="flex items-center gap-2">
                        <label for="gestion" class="text-sm font-extrabold text-blue-950 whitespace-nowrap">Gestión:</label>
                        <select name="gestion" id="gestion" onchange="aplicarFiltro(this.form)" class="bg-sky-50 border border-sky-200 text-blue-950 font-bold text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[170px] px-4 py-2.5 cursor-pointer">
                            <option value="">Todas las gestiones</option>
                            @foreach(range(date('Y'), date('Y')-5) as $year)
                                <option value="{{ $year }}" {{ request('gestion') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro Mes -->
                    <div class="flex items-center gap-2">
                        <label for="mes" class="text-sm font-extrabold text-blue-950 whitespace-nowrap">Mes:</label>
                        <select name="mes" id="mes" onchange="aplicarFiltro(this.form)" class="bg-sky-50 border border-sky-200 text-blue-950 font-bold text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[160px] px-4 py-2.5 cursor-pointer">
                            <option value="">Todos los meses</option>
                            @php
                                $listaMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                            @endphp
                            @foreach($listaMeses as $m)
                                <option value="{{ $m }}" {{ request('mes') == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('finanzas.index') }}" onclick="limpiarFiltrosGuardados()" class="text-xs font-bold text-red-500 hover:underline {{ (request('gestion') || request('mes')) ? '' : 'hidden' }}" id="btnLimpiar">
                        Limpiar Filtros
                    </a>

                    <!-- BOTÓN EXPORTAR A EXCEL -->
                    <a href="{{ route('finanzas.exportar', ['gestion' => request('gestion'), 'mes' => request('mes')]) }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl font-extrabold text-sm shadow-md transition-all duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        .Excel
                    </a>
                </div>
            </div>

            @if(session('success'))
            <div class="mb-6 p-4 bg-sky-100/90 border-l-4 border-sky-600 rounded-r-xl flex items-center shadow-md">
                <span class="text-blue-950 font-bold">{{ session('success') }}</span>
            </div>
            @endif

            <!-- Tabla de Balances -->
            <div class="bg-white rounded-2xl shadow-xl shadow-sky-900/10 overflow-hidden border border-sky-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-sky-100 table-fixed">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="w-1/6 px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Periodo</th>
                                <th class="w-1/6 px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Ingresos</th>
                                <th class="w-1/6 px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Egresos</th>
                                <th class="w-1/6 px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Saldo Final</th>
                                <th class="w-2/6 px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Detalles</th>
                                <th class="w-1/6 px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Respaldo</th>
                                @if($esAdmin)
                                <th class="w-1/6 px-6 py-4 text-right text-xs font-bold uppercase tracking-wider">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sky-100/70 bg-white">
                            @forelse($balancesOrdenados as $balance)
                            <tr class="hover:bg-sky-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-extrabold text-blue-950">{{ $balance->mes }}</div>
                                    <div class="text-xs text-blue-700 font-bold">Gestión {{ $balance->gestion }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-green-600">+ Bs. {{ number_format($balance->ingresos, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-red-600">- Bs. {{ number_format($balance->egresos, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1.5 inline-flex text-xs font-extrabold rounded-lg bg-sky-100 text-blue-950 border border-sky-200">
                                        Bs. {{ number_format($balance->saldo_final, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 max-w-xs break-all whitespace-normal">
                                    @php $detalle = $balance->detalle ?? 'Sin observaciones'; @endphp
                                    @if(strlen($detalle) > 45)
                                        <div class="text-xs text-sky-900 break-all leading-relaxed">
                                            <span class="detalle-corto block break-all">{{ Str::limit($detalle, 45, '') }}</span>
                                            <span class="detalle-completo hidden break-all">{{ $detalle }}</span>
                                            <button type="button" onclick="toggleDetalle(this)" class="text-blue-600 font-extrabold hover:underline block mt-1 focus:outline-none">
                                                ... ver más
                                            </button>
                                        </div>
                                    @else
                                        <p class="text-xs text-sky-900 break-all leading-relaxed">{{ $detalle }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($balance->comprobante_url)
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="{{ asset('storage/' . $balance->comprobante_url) }}" target="_blank" title="Ver documento"
                                               class="inline-flex items-center px-2.5 py-1 bg-sky-100 text-sky-800 rounded-lg font-bold text-xs hover:bg-sky-200 transition-all border border-sky-200">
                                                👁️ Ver
                                            </a>
                                            <a href="{{ asset('storage/' . $balance->comprobante_url) }}" download title="Descargar documento"
                                               class="inline-flex items-center px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg font-bold text-xs hover:bg-emerald-200 transition-all border border-emerald-200">
                                                📥 Descargar
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Sin respaldo</span>
                                    @endif
                                </td>
                                @if($esAdmin)
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('finanzas.edit', $balance->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs font-extrabold shadow-sm transition-all hover:scale-105">
                                            Editar
                                        </a>

                                        @if($esSuperAdmin)
                                        <form action="{{ route('finanzas.destroy', $balance->id) }}" method="POST" class="m-0 p-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-extrabold shadow-sm transition-all hover:scale-105" onclick="return confirm('¿Eliminar este balance?')">
                                                Eliminar
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $esAdmin ? '7' : '6' }}" class="px-6 py-12 text-center text-sky-700 font-extrabold text-base">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span>Sin registros para la búsqueda seleccionada</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script>
        function toggleDetalle(btn) {
            const container = btn.parentElement;
            const corto = container.querySelector('.detalle-corto');
            const completo = container.querySelector('.detalle-completo');

            if (completo.classList.contains('hidden')) {
                completo.classList.remove('hidden');
                corto.classList.add('hidden');
                btn.textContent = ' ver menos';
            } else {
                completo.classList.add('hidden');
                corto.classList.remove('hidden');
                btn.textContent = '... ver más';
            }
        }

        // --- MANEJO DE MEMORIA DE FILTROS ---
        function aplicarFiltro(form) {
            const gestion = form.gestion.value;
            const mes = form.mes.value;
            
            if (gestion) sessionStorage.setItem('finanzas_gestion', gestion);
            else sessionStorage.removeItem('finanzas_gestion');

            if (mes) sessionStorage.setItem('finanzas_mes', mes);
            else sessionStorage.removeItem('finanzas_mes');

            form.submit();
        }

        function limpiarFiltrosGuardados() {
            sessionStorage.removeItem('finanzas_gestion');
            sessionStorage.removeItem('finanzas_mes');
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Verificar si hay parámetros de búsqueda en la URL
            const urlParams = new URLSearchParams(window.location.search);
            const tieneGestionParam = urlParams.has('gestion');
            const tieneMesParam = urlParams.has('mes');

            const gestionGuardada = sessionStorage.getItem('finanzas_gestion');
            const mesGuardado = sessionStorage.getItem('finanzas_mes');

            // Si se volvió tras una acción sin params de URL, pero existe filtro guardado:
            if (!tieneGestionParam && !tieneMesParam && (gestionGuardada || mesGuardado)) {
                let url = new URL(window.location.href);
                if (gestionGuardada) url.searchParams.set('gestion', gestionGuardada);
                if (mesGuardado) url.searchParams.set('mes', mesGuardado);
                window.location.href = url.toString();
                return;
            }

            // --- APEXCHARTS CONFIG ---
            @php
                $mesesGrafico = [];
                $ingresosGrafico = [];
                $egresosGrafico = [];
                
                if(isset($balancesOrdenados)) {
                    foreach($balancesOrdenados as $b) {
                        $mesesGrafico[] = $b->mes . ' ' . $b->gestion;
                        $ingresosGrafico[] = (float)$b->ingresos;
                        $egresosGrafico[] = (float)$b->egresos;
                    }
                }
            @endphp

            const totalDatos = @json(count($mesesGrafico));
            const chartElement = document.querySelector("#chartFinanzas");

            if (totalDatos > 12) {
                chartElement.style.width = (totalDatos * 80) + "px";
            } else {
                chartElement.style.width = "100%";
            }

            var options = {
                series: [{
                    name: 'Ingresos (Bs.)',
                    data: @json($ingresosGrafico)
                }, {
                    name: 'Gastos (Bs.)',
                    data: @json($egresosGrafico)
                }],
                chart: {
                    type: 'bar',
                    height: 330,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: totalDatos > 12 ? '75%' : (totalDatos <= 3 ? '35%' : '60%'),
                        maxColumnWidth: totalDatos <= 12 ? 60 : 45,
                        borderRadius: 4
                    },
                },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                colors: ['#10B981', '#EF4444'],
                xaxis: {
                    categories: @json($mesesGrafico),
                    labels: {
                        rotate: totalDatos > 12 ? -45 : 0,
                        style: { fontSize: '11px', fontWeight: 600 }
                    }
                },
                yaxis: {
                    title: { text: 'Monto en Bs.', style: { fontSize: '11px', fontWeight: 600 } },
                    labels: {
                        formatter: function (val) {
                            return val.toLocaleString();
                        }
                    }
                },
                fill: { opacity: 1 },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "Bs. " + val.toFixed(2)
                        }
                    }
                }
            };

            var chart = new ApexCharts(chartElement, options);
            chart.render();
        });
    </script>
</x-app-layout>