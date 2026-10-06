<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'ci',
        'telefono',
        'rol_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // Relación: Un socio puede tener muchas lecturas de agua
    public function lecturas()
    {
        return $this->hasMany(Lectura::class);
    }

    /**
     * Relación: Un socio tiene muchas multas
     */
    public function multas(): HasMany
    {
        return $this->hasMany(Multa::class, 'socio_id');
    }

    /**
     * Verificar si el usuario es Admin o SuperAdmin
     */
    public function esAdmin(): bool
    {
        return in_array($this->rol_id, [1, 2]) || 
               in_array($this->email, ['jonh@example.com', 'adolfo@example.com']);
    }
}