<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Message extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'messages';

    protected $fillable = [
        'contenu',
        'date',
        'statut', // e.g., "lu", "non lu"
        'sender_id',
        'receiver_id'
    ];

    public function sender()
    {
        return $this->belongsTo(Utilisateur::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Utilisateur::class, 'receiver_id');
    }
}
