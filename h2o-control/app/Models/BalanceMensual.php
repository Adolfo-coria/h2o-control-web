<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceMensual extends Model
{
    use HasFactory;

    // 👇 AQUÍ ESTÁ EL CAMBIO (Quitamos la 'e' de mensuales) 👇
    protected $table = 'balance_mensuals';

    // 2. Le decimos qué columnas se pueden llenar de forma segura desde el formulario
    protected $fillable = [
        'mes',
        'gestion',
        'ingresos',
        'egresos',
        'saldo_final',
        'detalle',
        'comprobante_url'
    ];
}