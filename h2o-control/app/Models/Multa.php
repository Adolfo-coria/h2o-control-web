<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Multa extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tarifa_multa_id',
        'tipo_multa',
        'monto',
        'motivo',
        'fecha_multa',
        'estado',
        'fecha_pago',
        'comprobante_pago'
    ];

    public function socio()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tarifa()
    {
        return $this->belongsTo(TarifaMulta::class, 'tarifa_multa_id');
    }
}