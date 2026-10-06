<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-xl text-slate-800 leading-tight flex items-center gap-2 tracking-tight">
            ⚙️ Panel Global del Super Administrador
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-gradient-to-r from-slate-800 via-indigo-950 to-slate-900 p-6 rounded-2xl shadow-xl shadow-indigo-950/10 text-white border border-slate-700/30">
                <h3 class="text-xl font-black tracking-tight">¡Bienvenido al Control Total del Sistema!</h3>
                <p class="text-sm text-indigo-200 mt-1 font-medium">Desde aquí puedes gestionar los accesos globales, auditar los roles de usuarios y supervisar los módulos de la plataforma de la OTB.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="bg-white p-6 rounded-2xl shadow-md shadow-slate-200/50 border border-slate-100 flex flex-col justify-between hover:shadow-lg transition-all duration-200">
                    <div>
                        <div class="text-3xl mb-3 filter drop-shadow-sm">👥</div>
                        <h4 class="font-extrabold text-slate-900 text-lg tracking-tight">Control de Socios</h4>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed font-medium">Administra las cuentas de todos los vecinos de la OTB, altas, bajas y asignación de medidores.</p>
                    </div>
                    <div class="mt-5">
                        <a href="{{ route('usuarios.index') }}" class="inline-block w-full text-center bg-indigo-650 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-2.5 px-4 rounded-xl text-xs shadow-md shadow-indigo-200 transition-all active:scale-[0.98]">
                            Gestionar Usuarios
                        </a>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-md shadow-slate-200/50 border border-slate-100 flex flex-col justify-between hover:shadow-lg transition-all duration-200">
                    <div>
                        <div class="text-3xl mb-3 filter drop-shadow-sm">💧</div>
                        <h4 class="font-extrabold text-slate-900 text-lg tracking-tight">Lecturas de Agua</h4>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed font-medium">Supervisión general de los consumos mensuales y estados de cuentas registrados en los medidores.</p>
                    </div>
                    <div class="mt-5">
                        <a href="{{ route('lecturas.index') }}" class="inline-block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-extrabold py-2.5 px-4 rounded-xl text-xs shadow-md shadow-blue-200 transition-all active:scale-[0.98]">
                            Ver Medidores
                        </a>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-md shadow-slate-200/50 border border-slate-100 flex flex-col justify-between opacity-85">
                    <div>
                        <div class="text-3xl mb-3 filter drop-shadow-sm">🔒</div>
                        <h4 class="font-extrabold text-slate-900 text-lg tracking-tight">Roles y Permisos</h4>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed font-medium">Configuración avanzada de la seguridad informática de la base de datos y niveles de acceso.</p>
                    </div>
                    <div class="mt-5">
                        <button class="w-full bg-slate-100 text-slate-400 font-extrabold py-2.5 px-4 rounded-xl text-xs border border-slate-200/60 cursor-not-allowed flex items-center justify-center gap-1.5" disabled>
                            <span>🚫</span> Módulo Protegido
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>