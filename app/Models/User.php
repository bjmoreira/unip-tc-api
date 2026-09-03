<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Estudante.
 *
 * O modelo de conhecimento (proficiencias) e o historico de provas ficam
 * vinculados a conta, e nao a uma sessao de navegacao. E isso que permite ao
 * estudante retomar o estudo de onde parou, em qualquer dispositivo, sem que o
 * algoritmo volte a estimar sua proficiencia do zero.
 */
#[Fillable(['name', 'email', 'password', 'tamanho_prova'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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

    public function proficiencias()
    {
        return $this->hasMany(Proficiencia::class, 'user_id');
    }

    public function provas()
    {
        return $this->hasMany(Prova::class, 'user_id');
    }
}
