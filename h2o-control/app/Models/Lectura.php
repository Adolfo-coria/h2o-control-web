<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lectura extends Model
{
    use HasFactory;

    // Permitimos que estos campos reciban datos
    protected $fillable = [
        'user_id',
        'mes',
        'gestion',
        'lectura_anterior',
        'lectura_actual',
        'consumo',
    ];

    // Relación: Una lectura pertenece a un Socio (Usuario)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
