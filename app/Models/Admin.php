<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Admin extends Utilisateur
{
    protected $connection = 'mongodb';
    protected $collection = 'utilisateurs';

    public static function boot()
    {
        parent::boot();
        static::creating(function ($admin) {
            $admin->role = 'admin';
        });
    }
}
