<script src="https://cdn.tailwindcss.com"></script>

<x-app-layout>
    <div class="py-12 bg-sky-200/70 min-h-screen">
        
        <div class="max-w-[98%] mx-auto px-4 space-y-6">
            
            <!-- Encabezado y Acción -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-extrabold text-3xl text-blue-950 leading-tight tracking-tight drop-shadow-sm">
                        {{ __('Control de Lecturas de Agua (Medidores)') }}
                    </h2>
                    <p class="text-sm text-sky-900 mt-1 font-bold">Panel de control y administración de consumo de la OTB</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <!-- 📊 BOTÓN DE EXPORTACIÓN A EXCEL -->
                    <a href="{{ route('lecturas.exportar') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl font-extrabold shadow-lg shadow-emerald-500/30 transition-all gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        .Excel
                    </a>

                    <a href="{{ route('lecturas.create') }}" class="inline-flex items-center justify-center bg-blue-700 hover:bg-blue-800 text-white px-5 py-3 rounded-xl font-extrabold shadow-lg shadow-blue-400/50 transition-all">
                        + Registrar Nueva Lectura
                    </a>
                </div>
            </div>

            <!-- Cálculos para Tarjetas de Resumen -->
            @php
                $totalPendiente = $lecturas->where('estado', 'pendiente')->sum(function($l) {
                    return ((float)$l->lectura_actual - (float)$l->lectura_anterior) * 2;
                });

                $totalCobrado = $lecturas->where('estado', 'pagado')->sum(function($l) {
                    return ((float)$l->lectura_actual - (float)$l->lectura_anterior) * 2;
                });

                $cantPendientes = $lecturas->where('estado', 'pendiente')->count();
            @endphp

            <!-- Tarjetas de Resumen -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Tarjeta 1: Pendientes de Cobro -->
                <div class="bg-white rounded-2xl p-6 shadow-md border-l-8 border-amber-500 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-sky-600 uppercase tracking-wider">Pendientes de Cobro</p>
                        <h3 class="text-3xl font-black text-amber-600 mt-1">Bs. {{ number_format($totalPendiente ?? 0, 2) }}</h3>
                        <p class="text-xs font-bold text-gray-500 mt-1">{{ $cantPendientes ?? 0 }} lecturas pendientes</p>
                    </div>
                    <div class="p-3 bg-amber-100 rounded-full text-amber-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Tarjeta 2: Total Cobrado -->
                <div class="bg-white rounded-2xl p-6 shadow-md border-l-8 border-green-500 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-sky-600 uppercase tracking-wider">Total Cobrado</p>
                        <h3 class="text-3xl font-black text-green-600 mt-1">Bs. {{ number_format($totalCobrado ?? 0, 2) }}</h3>
                        <p class="text-xs font-bold text-gray-500 mt-1">Ingresado al Balance Comunal</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full text-green-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Tarjeta 3: Estado del Sistema -->
                <div class="bg-white rounded-2xl p-6 shadow-md border-l-8 border-blue-600 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-sky-600 uppercase tracking-wider">Estado del Sistema</p>
                        <h3 class="text-3xl font-black text-blue-950 mt-1">Activo</h3>
                        <p class="text-xs font-bold text-gray-500 mt-1">Control de Agua OTB</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- FILTRADO FLOTANTE -->
            <div class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- 1. Filtro por Socio -->
                    <div>
                        <label class="block text-xs font-black uppercase text-blue-950 tracking-wider mb-1.5 ml-1">
                            Socio / Usuario
                        </label>
                        <input type="text" id="filtro-socio" placeholder="🔍 Buscar por nombre..." 
                               class="w-full px-4 py-2.5 bg-white/90 focus:bg-white rounded-xl border border-sky-300 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-sm font-semibold text-blue-950 shadow-sm transition-all placeholder-sky-800/50">
                    </div>

                    <!-- 2. Filtro por Mes / Gestión -->
                    <div>
                        <label class="block text-xs font-black uppercase text-blue-950 tracking-wider mb-1.5 ml-1">
                            Mes / Gestión
                        </label>
                        <input type="text" id="filtro-periodo" placeholder="📅 Ej: Enero / 2024..." 
                               class="w-full px-4 py-2.5 bg-white/90 focus:bg-white rounded-xl border border-sky-300 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-sm font-semibold text-blue-950 shadow-sm transition-all placeholder-sky-800/50">
                    </div>

                    <!-- 3. Filtro por Estado -->
                    <div>
                        <label class="block text-xs font-black uppercase text-blue-950 tracking-wider mb-1.5 ml-1">
                            Estado del Pago
                        </label>
                        <select id="filtro-estado" 
                                class="w-full px-4 py-2.5 bg-white/90 focus:bg-white rounded-xl border border-sky-300 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-sm font-semibold text-blue-950 shadow-sm transition-all cursor-pointer">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="pagado">Pagados</option>
                        </select>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div class="p-4 bg-sky-100/90 border-l-4 border-sky-600 rounded-r-xl flex items-center shadow-md">
                <span class="text-blue-950 font-bold">{{ session('success') }}</span>
            </div>
            @endif

            <!-- Tabla de Lecturas -->
            <div class="bg-white rounded-2xl shadow-xl shadow-sky-900/10 border border-sky-100 overflow-hidden w-full">
                <table class="w-full divide-y divide-sky-100">
                    <thead class="bg-blue-900 text-white text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-4 text-left">Socio</th>
                            <th class="px-3 py-4 text-left whitespace-nowrap">Mes / Gestión</th>
                            <th class="px-3 py-4 text-center whitespace-nowrap">L. Anterior</th>
                            <th class="px-3 py-4 text-center whitespace-nowrap">L. Actual</th>
                            <th class="px-3 py-4 text-center whitespace-nowrap">Consumo</th>
                            <th class="px-3 py-4 text-left whitespace-nowrap">Monto (Bs.)</th>
                            <th class="px-3 py-4 text-center whitespace-nowrap">Estado</th>
                            <th class="px-4 py-4 text-center whitespace-nowrap w-[200px]">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sky-100/70 bg-white text-sm">
                        
                        @php
                            $mesesNumeros = [
                                'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
                                'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
                                'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
                            ];

                            $lecturasOrdenadasCronologico = $lecturas->sort(function($a, $b) use ($mesesNumeros) {
                                if ($a->gestion != $b->gestion) {
                                    return $a->gestion <=> $b->gestion;
                                }
                                $numMesA = $mesesNumeros[$a->mes] ?? 0;
                                $numMesB = $mesesNumeros[$b->mes] ?? 0;
                                return $numMesA <=> $numMesB;
                            });
                        @endphp

                        @forelse($lecturasOrdenadasCronologico as $lectura)
                        <tr class="fila-lectura hover:bg-sky-50 transition-colors duration-150">
                            
                            <td class="px-4 py-4 font-extrabold text-blue-950 nombre-socio">
                                {{ $lectura->usuario->name ?? 'Desconocido' }}
                            </td>
                            
                            <td class="px-3 py-4 text-blue-900 font-bold whitespace-nowrap periodo-lectura">
                                {{ $lectura->mes }} / {{ $lectura->gestion }}
                            </td>
                            
                            <td class="px-3 py-4 text-center text-sky-700 font-semibold whitespace-nowrap">
                                {{ number_format((float)$lectura->lectura_anterior, 1, '.', '') }} m³
                            </td>
                            
                            <td class="px-3 py-4 text-center font-black text-blue-950 whitespace-nowrap">
                                {{ number_format((float)$lectura->lectura_actual, 1, '.', '') }} m³
                            </td>
                            
                            <td class="px-3 py-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs font-extrabold rounded-lg bg-sky-100 text-blue-950 border border-sky-200">
                                    {{ number_format((float)$lectura->lectura_actual - (float)$lectura->lectura_anterior, 1, '.', '') }} m³
                                </span>
                            </td>
                            
                            <td class="px-3 py-4 font-extrabold text-emerald-600 whitespace-nowrap">
                                Bs. {{ number_format(((float)$lectura->lectura_actual - (float)$lectura->lectura_anterior) * 2, 2, ',', '.') }}
                            </td>
                            
                            <td class="px-3 py-4 text-center whitespace-nowrap estado-lectura" data-estado="{{ strtolower($lectura->estado ?? 'pendiente') }}">
                                @if(($lectura->estado ?? 'pendiente') == 'pendiente')
                                    <span class="px-3 py-1 inline-flex text-xs font-extrabold rounded-full bg-red-100 text-red-800 border border-red-200 items-center">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5 animate-pulse"></span>
                                        Pendiente
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 items-center">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        Pagado
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-end gap-2 pr-4">
                                    @if(($lectura->estado ?? 'pendiente') == 'pendiente')
                                        <form action="{{ route('lecturas.pagar', $lectura->id) }}" method="POST" class="m-0 p-0 flex">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-sm transition-all hover:scale-105 h-8"
                                                    title="Confirmar Pago">
                                                💵 <span class="hidden lg:inline">Confirmar Pago</span>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('lecturas.edit', $lectura->id) }}" 
                                       class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-bold shadow-sm transition-all hover:scale-105 h-8"
                                       title="Editar Registro">
                                        ✏️ <span class="hidden lg:inline">Editar</span>
                                    </a>
                                    
                                    <form action="{{ route('lecturas.destroy', $lectura->id) }}" method="POST" class="m-0 p-0 flex">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-xl text-xs font-bold shadow-sm transition-all hover:scale-105 h-8" 
                                                onclick="return confirm('¿Eliminar registro?')"
                                                title="Eliminar Registro">
                                            🗑️ <span class="hidden lg:inline">Borrar</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-sky-600 font-bold">
                                No hay lecturas registradas
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- SCRIPT DE FILTRADO MULTI-CAMPO -->
    <script>
        const inputSocio = document.getElementById('filtro-socio');
        const inputPeriodo = document.getElementById('filtro-periodo');
        const selectEstado = document.getElementById('filtro-estado');
        const filas = document.querySelectorAll('.fila-lectura');

        function aplicarFiltros() {
            let valSocio = inputSocio.value.toLowerCase().trim();
            let valPeriodo = inputPeriodo.value.toLowerCase().trim();
            let valEstado = selectEstado.value.toLowerCase().trim();

            filas.forEach(function(fila) {
                let elSocio = fila.querySelector('.nombre-socio');
                let elPeriodo = fila.querySelector('.periodo-lectura');
                let elEstado = fila.querySelector('.estado-lectura');

                let txtSocio = elSocio ? elSocio.textContent.toLowerCase().trim() : "";
                let txtPeriodo = elPeriodo ? elPeriodo.textContent.toLowerCase().trim() : "";
                let txtEstado = elEstado ? elEstado.getAttribute('data-estado') : "";

                let cumpleSocio = valSocio === "" || txtSocio.includes(valSocio);
                let cumplePeriodo = valPeriodo === "" || txtPeriodo.includes(valPeriodo);
                let cumpleEstado = valEstado === "" || txtEstado === valEstado;

                if (cumpleSocio && cumplePeriodo && cumpleEstado) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }

        inputSocio.addEventListener('input', aplicarFiltros);
        inputPeriodo.addEventListener('input', aplicarFiltros);
        selectEstado.addEventListener('change', aplicarFiltros);
    </script>
</x-app-layout>