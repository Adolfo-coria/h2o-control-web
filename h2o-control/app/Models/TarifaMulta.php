<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifaMulta extends Model
{
    use HasFactory;

    protected $table = 'tarifas_multas';

    protected $fillable = [
        'nombre',
        'monto_predeterminado',
        'descripcion'
    ];
}