<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nome',
        'name',
        'login',
        'email',
        'password',
        'equipe_id',
        'role_id',
        'ativo',
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
            'last_login_at' => 'datetime',
            'ativo' => 'boolean',
        ];
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['nome'] ?? null;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['nome'] = $value;

        if (
            $value !== null
            && trim($value) !== ''
            && empty($this->attributes['login'])
        ) {
            $this->attributes['login'] = Str::lower(trim($value));
        }
    }
}
