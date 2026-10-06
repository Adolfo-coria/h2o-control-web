<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LecturaController; 
use App\Http\Controllers\BalanceMensualController; 
use App\Http\Controllers\MultaController; 
use App\Http\Controllers\TarifaMultaController; 
use App\Http\Controllers\ReporteController; 
use Illuminate\Support\Facades\Route;

// REDIRECCIÓN AUTOMÁTICA AL LOGIN AL INGRESAR A LA RAÍZ (/)
Route::get('/', function () {
    return redirect()->route('login');
});

// CONTROLADOR DEL DASHBOARD
Route::get('/dashboard', function () {
    if (auth()->check()) {
        $user = auth()->user();

        // 1. PUENTE DIRECTO PARA EL ADMINISTRADOR
        if ($user->email === 'jonh@example.com' || $user->name === 'Jonh Coria Flores') { 
            return redirect()->route('lecturas.index');
        }

        // 2. PUENTE DIRECTO PARA SUPERADMIN
        if ($user->email === 'adolfo@example.com' || $user->name === 'Adolfo Coria') { 
            return redirect()->route('usuarios.index');
        }

        // LÓGICA NORMAL DE RESPALDO
        if ($user->rol_id == 3) { 
            return redirect()->route('usuarios.index');
        }

        if ($user->rol_id == 1) { 
            return redirect()->route('lecturas.index');
        }

        return redirect()->route('socio.consumo');
    }
    return redirect('/login');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    
    // EXPORTACIONES (SIEMPRE VAN ANTES DE LOS RESOURCE)
    Route::get('/usuarios/exportar', [UserController::class, 'exportar'])->name('usuarios.exportar');
    Route::get('/lecturas/exportar', [LecturaController::class, 'exportar'])->name('lecturas.exportar');
    Route::get('/finanzas/exportar', [BalanceMensualController::class, 'exportar'])->name('finanzas.exportar');
    Route::get('/multas/exportar', [MultaController::class, 'exportar'])->name('multas.exportar');
    Route::get('/reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');

    // MÓDULO DE REPORTES (SOLO ADMIN/SUPERADMIN)
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

    // Gestión de socios
    Route::resource('usuarios', UserController::class);

    // RUTA AJAX: Obtener la última lectura
    Route::get('/lecturas/ultima/{usuario_id}', [LecturaController::class, 'obtenerUltimaLectura'])->name('lecturas.ultima');

    // Módulo de cobro asincrónico y vistas del socio
    Route::patch('/lecturas/{lectura}/pagar', [LecturaController::class, 'pagar'])->name('lecturas.pagar');
    Route::get('/mi-consumo', [LecturaController::class, 'miConsumo'])->name('socio.consumo');

    // CRUD de Lecturas de agua
    Route::resource('lecturas', LecturaController::class);

    // FINANZAS (Balances Mensuales)
    Route::resource('finanzas', BalanceMensualController::class);

    // MÓDULO DE MULTAS
    Route::get('/multas', [MultaController::class, 'index'])->name('multas.index');
    Route::get('/multas/crear', [MultaController::class, 'create'])->name('multas.create');
    Route::post('/multas', [MultaController::class, 'store'])->name('multas.store');
    Route::get('/multas/{id}/editar', [MultaController::class, 'edit'])->name('multas.edit');
    Route::put('/multas/{id}', [MultaController::class, 'update'])->name('multas.update');
    Route::delete('/multas/{id}', [MultaController::class, 'destroy'])->name('multas.destroy');
    Route::post('/multas/{id}/pagar', [MultaController::class, 'pagar'])->name('multas.pagar');
    Route::post('/multas/{id}/condonar', [MultaController::class, 'condonar'])->name('multas.condonar');

    // CRUD DE TARIFAS
    Route::resource('tarifas-multas', TarifaMultaController::class)->names([
        'index'   => 'tarifas-multas.index',
        'store'   => 'tarifas-multas.store',
        'update'  => 'tarifas-multas.update',
        'destroy' => 'tarifas-multas.destroy',
    ]);
    
    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/actualizar-qr', [LecturaController::class, 'actualizarQr'])->name('qr.actualizar');
});

require __DIR__.'/auth.php';