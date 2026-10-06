<script src="https://cdn.tailwindcss.com"></script>
<x-app-layout>
    <div class="py-12 bg-sky-200/70 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div class="md:flex md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-extrabold text-3xl text-blue-950 leading-tight tracking-tight drop-shadow-sm">
                        Gestión de Socios
                    </h2>
                    <p class="text-sm text-sky-900 mt-1 font-bold">Panel de control y administración de usuarios de la OTB</p>
                </div>
                
                <div class="mt-4 md:mt-0 flex flex-wrap gap-3 items-center">
                    <!-- BOTÓN DE DESCARGA: Exclusivo para Admin y Superadmin -->
                    @if(auth()->check() && (str_contains(strtolower(auth()->user()->rol->nombre ?? ''), 'admin') || str_contains(strtolower(auth()->user()->rol->nombre ?? ''), 'super')))
                        <a href="{{ route('usuarios.exportar') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3.5 rounded-xl font-extrabold shadow-lg shadow-emerald-400/50 transition-all duration-200 ease-in-out transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            .Excel
                        </a>
                    @endif

                    <a href="{{ route('usuarios.create') }}" class="inline-flex items-center justify-center bg-blue-700 hover:bg-blue-800 text-white px-6 py-3.5 rounded-xl font-extrabold shadow-lg shadow-blue-400/50 transition-all duration-200 ease-in-out transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Registrar Nuevo Socio
                    </a>
                </div>
            </div>

            <!-- FILTROS SIN FONDO BLANCO -->
            <div class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <!-- 1. Buscar por Nombre -->
                    <div>
                        <label class="block text-xs font-black uppercase text-blue-950 tracking-wider mb-1.5 ml-1">
                            Nombre Completo
                        </label>
                        <input type="text" id="filtro-nombre" placeholder="🔍 Buscar por nombre..." 
                               class="w-full px-4 py-2.5 bg-white/90 focus:bg-white rounded-xl border border-sky-300 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-sm font-semibold text-blue-950 shadow-sm transition-all placeholder-sky-800/50">
                    </div>

                    <!-- 2. Buscar por CI -->
                    <div>
                        <label class="block text-xs font-black uppercase text-blue-950 tracking-wider mb-1.5 ml-1">
                            Carnet de Identidad
                        </label>
                        <input type="text" id="filtro-ci" placeholder="💳 Buscar por CI..." 
                               class="w-full px-4 py-2.5 bg-white/90 focus:bg-white rounded-xl border border-sky-300 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-sm font-semibold text-blue-950 shadow-sm transition-all placeholder-sky-800/50">
                    </div>

                    <!-- 3. Buscar por Teléfono -->
                    <div>
                        <label class="block text-xs font-black uppercase text-blue-950 tracking-wider mb-1.5 ml-1">
                            Teléfono / WhatsApp
                        </label>
                        <input type="text" id="filtro-telefono" placeholder="📱 Buscar por teléfono..." 
                               class="w-full px-4 py-2.5 bg-white/90 focus:bg-white rounded-xl border border-sky-300 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-sm font-semibold text-blue-950 shadow-sm transition-all placeholder-sky-800/50">
                    </div>

                </div>
            </div>

            @if(session('success'))
            <div class="mb-6 p-4 bg-sky-100/90 border-l-4 border-sky-600 rounded-r-xl flex items-center shadow-md">
                <svg class="w-6 h-6 text-sky-700 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-blue-950 font-bold">{{ session('success') }}</span>
            </div>
            @endif

            <div class="bg-white rounded-2xl shadow-xl shadow-sky-900/10 overflow-hidden border border-sky-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-sky-100">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Nombre Completo</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Carnet de Identidad</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Teléfono / WhatsApp</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Rol Asignado</th>
                                <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sky-100/70 bg-white">
                            
                            @forelse($usuarios->sortBy('name') as $user)
                            <tr class="fila-socio hover:bg-sky-50 transition-colors duration-150">
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 flex items-center justify-center rounded-xl bg-gradient-to-br from-sky-400 to-blue-600 text-white font-black text-lg shadow-md shadow-blue-500/20">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-extrabold text-blue-950 nombre-socio">{{ $user->name }}</div>
                                            <div class="text-xs text-blue-700 font-bold">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="ci-socio px-3 py-1.5 inline-flex text-xs font-extrabold rounded-lg bg-sky-100 text-blue-950 border border-sky-200 shadow-sm">
                                        {{ $user->ci ?? 'N/A' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->telefono)
                                    <div class="flex items-center text-sm font-bold text-blue-950 telefono-socio">
                                        <span class="w-2 h-2 rounded-full bg-sky-500 mr-2 animate-pulse"></span>
                                        {{ $user->telefono }}
                                    </div>
                                    @else
                                    <span class="telefono-socio text-sm text-sky-500 italic font-semibold">No registrado</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $rolNombre = $user->rol->nombre ?? ($user->rol->nombre_rol ?? ($user->rol->name ?? 'Socio Común'));
                                        
                                        if (str_contains(strtolower($rolNombre), 'super')) {
                                            $badgeClasses = 'bg-blue-800 text-white font-black shadow-md shadow-blue-500/20';
                                        } elseif (str_contains(strtolower($rolNombre), 'admin')) {
                                            $badgeClasses = 'bg-sky-600 text-white font-black shadow-md shadow-sky-500/20';
                                        } else {
                                            $badgeClasses = 'bg-sky-100 text-blue-800 border-sky-200 font-bold';
                                        }
                                    @endphp
                                    <span class="px-3 py-1.5 inline-flex text-xs uppercase tracking-wide rounded-full border border-transparent {{ $badgeClasses }}">
                                        {{ $rolNombre }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-3">
                                        <a href="{{ route('usuarios.edit', $user->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs font-extrabold shadow-sm transition-all hover:scale-105">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Editar
                                        </a>
                                        
                                        <form action="{{ route('usuarios.destroy', $user->id) }}" method="POST" class="m-0 p-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-extrabold shadow-sm transition-all hover:scale-105" onclick="return confirm('¿Seguro que quiere eliminar este socio?')">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sky-600">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-sky-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <p class="text-base font-bold">No hay socios registrados</p>
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

    <!-- SCRIPT DE FILTRADO MULTI-CAMPO -->
    <script>
        const inputNombre = document.getElementById('filtro-nombre');
        const inputCi = document.getElementById('filtro-ci');
        const inputTelefono = document.getElementById('filtro-telefono');
        const filas = document.querySelectorAll('.fila-socio');

        function aplicarFiltros() {
            let valNombre = inputNombre.value.toLowerCase().trim();
            let valCi = inputCi.value.toLowerCase().trim();
            let valTelefono = inputTelefono.value.toLowerCase().trim();

            filas.forEach(function(fila) {
                let elNombre = fila.querySelector('.nombre-socio');
                let elCi = fila.querySelector('.ci-socio');
                let elTelefono = fila.querySelector('.telefono-socio');

                let txtNombre = elNombre ? elNombre.textContent.toLowerCase().trim() : "";
                let txtCi = elCi ? elCi.textContent.toLowerCase().trim() : "";
                let txtTelefono = elTelefono ? elTelefono.textContent.toLowerCase().trim() : "";

                let cumpleNombre = valNombre === "" || txtNombre.startsWith(valNombre);
                let cumpleCi = valCi === "" || txtCi.includes(valCi);
                let cumpleTelefono = valTelefono === "" || txtTelefono.includes(valTelefono);

                if (cumpleNombre && cumpleCi && cumpleTelefono) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }

        inputNombre.addEventListener('input', aplicarFiltros);
        inputCi.addEventListener('input', aplicarFiltros);
        inputTelefono.addEventListener('input', aplicarFiltros);
    </script>
</x-app-layout>