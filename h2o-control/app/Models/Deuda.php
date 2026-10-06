<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deuda extends Model
{
    use HasFactory;

    // Con esto le damos permiso a Laravel para guardar estos campos en masa
    protected $fillable = [
        'user_id',
        'lectura_id',
        'monto',
        'estado',
    ];

    // Relación por si necesitas saber de quién es la deuda
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}