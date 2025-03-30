<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // Specify the MongoDB connection
    protected $connection = 'mongodb';
    protected $fillable = [
        'contenu',      
        'dateEnvoi',   
        'senderId'      
    ];

    // Cast dateEnvoi as a Carbon instance
    protected $casts = [
        'dateEnvoi' => 'datetime',
    ];
}