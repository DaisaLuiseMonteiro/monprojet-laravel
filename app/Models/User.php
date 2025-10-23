<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relation avec Admin
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    // Relation avec Client
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    // Méthodes pour vérifier le statut
    public function isAdmin()
    {
        return $this->admin !== null;
    }

    public function isClient()
    {
        return $this->client !== null;
    }

    // Méthode pour obtenir le type d'utilisateur
    public function getTypeAttribute()
    {
        if ($this->isAdmin()) {
            return 'admin';
        } elseif ($this->isClient()) {
            return 'client';
        }

        return 'user';
    }
}
