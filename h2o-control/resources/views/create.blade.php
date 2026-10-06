<script src="https://cdn.tailwindcss.com"></script>
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-blue-950 leading-tight tracking-tight drop-shadow-sm">
                    {{ __('Registrar Consumo de Agua') }}
                </h2>
                <p class="text-sm text-sky-800 mt-1 font-semibold">Ingrese los datos del medidor del socio para el cálculo mensual</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-sky-100 via-blue-50 to-sky-200 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-2xl shadow-sky-900/20 rounded-2xl border border-sky-100">
                <div class="p-8 text-gray-900">
                    
                    <form action="{{ route('lecturas.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-5">
                            <label class="block text-blue-950 text-sm font-extrabold mb-2">Seleccionar Socio de la OTB</label>
                            <select name="user_id" class="shadow-sm appearance-none border border-sky-200 rounded-xl w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all font-semibold" required>
                                <option value="" class="font-semibold text-gray-400">-- Seleccione un vecino --</option>
                                @foreach($socios as $socio)
                                    <option value="{{ $socio->id }}" class="font-semibold text-slate-800">{{ $socio->name }} (C.I. {{ $socio->ci ?? 'S/N' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label class="block text-blue-950 text-sm font-extrabold mb-2">Mes de Consumo</label>
                                <select name="mes" class="shadow-sm appearance-none border border-sky-200 rounded-xl w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all font-semibold" required>
                                    <option value="Enero">Enero</option>
                                    <option value="Febrero">Febrero</option>
                                    <option value="Marzo">Marzo</option>
                                    <option value="Abril">Abril</option>
                                    <option value="Mayo">Mayo</option>
                                    <option value="Junio" selected>Junio</option>
                                    <option value="Julio">Julio</option>
                                    <option value="Agosto">Agosto</option>
                                    <option value="Septiembre">Septiembre</option>
                                    <option value="Octubre">Octubre</option>
                                    <option value="Noviembre">Noviembre</option>
                                    <option value="Diciembre">Diciembre</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-blue-950 text-sm font-extrabold mb-2">Gestión (Año)</label>
                                <input type="number" name="gestion" value="2026" class="shadow-sm appearance-none border border-sky-200 rounded-xl w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all font-bold text-center bg-sky-50/50" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                            <div>
                                <label class="block text-blue-950 text-sm font-extrabold mb-2">Lectura Anterior ($m^3$)</label>
                                <input type="number" name="lectura_anterior" id="lectura_anterior" value="0" min="0" class="shadow-sm appearance-none border border-sky-200 rounded-xl w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all font-bold" required>
                                <p class="text-xs text-sky-600 font-semibold mt-1.5">Si es el primer registro del socio, dejar en 0.</p>
                            </div>
                            <div>
                                <label class="block text-blue-950 text-sm font-extrabold mb-2">Lectura Actual ($m^3$)</label>
                                <input type="number" name="lectura_actual" id="lectura_actual" min="0" class="shadow-sm appearance-none border border-blue-400 rounded-xl w-full py-3 px-4 text-blue-950 font-black leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200 transition-all bg-blue-50/30" required>
                            </div>
                        </div>

                        <div class="flex items-center justify-end border-t border-sky-100 pt-6 gap-4">
                            <a href="{{ route('lecturas.index') }}" class="inline-flex items-center justify-center bg-slate-600 hover:bg-slate-700 text-white px-6 py-3 rounded-xl font-extrabold shadow-md transition-all duration-150 transform hover:-translate-y-0.5">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-extrabold shadow-lg shadow-emerald-200 transition-all duration-150 transform hover:-translate-y-0.5 cursor-pointer">
                                Guardar Lectura
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>