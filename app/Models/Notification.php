<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Notification extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'notifications';

    protected $fillable = [
        'contenu',
        'date',
        'statut', // e.g., "vue", "non vue"
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(Utilisateur::class, 'user_id');
    }
}
