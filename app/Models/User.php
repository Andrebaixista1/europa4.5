<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'empresa',
        'login',
        'nome',
        'email',
        'celular',
        'senha',
        'status',
        'nivel',
        'hierarquia',
        'empresa_id',
        'vencimento',
        'preco',
    ];

    protected $hidden = [
        'senha',
    ];

    protected $casts = [
        'vencimento' => 'datetime',
        'criacao' => 'datetime',
        'atualizacao' => 'datetime',
        'ultimo_log' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function getAuthIdentifierName()
    {
        return 'login';
    }
    
    // Map Laravel's expected timestamps to the table's columns
    const CREATED_AT = 'criacao';
    const UPDATED_AT = 'atualizacao';

    /**
     * Get the hierarquia associated with the user.
     */
    public function hierarquia()
    {
        return $this->belongsTo(Hierarquia::class, 'hierarquia', 'id');
    }

    /**
     * Get the empresa associated with the user.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }

    /**
     * Check if user is admin (hierarquia = 1).
     */
    public function isAdmin()
    {
        return $this->hierarquia == 1;
    }

    /**
     * Check if user can view all companies' data.
     */
    public function canViewAllCompanies()
    {
        return $this->isAdmin();
    }

    /**
     * Get the company ID filter for queries.
     * Returns null for admins (no filter), empresa_id for regular users.
     */
    public function getCompanyFilter()
    {
        return $this->isAdmin() ? null : $this->empresa_id;
    }
}
