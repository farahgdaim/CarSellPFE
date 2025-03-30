<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'conversations';

    protected $fillable = [
        'Ref_id_user1',
        'Ref_id_user2',
        'messages'
    ];

    protected $attributes = [
        'messages' => [] // Default messages as an empty array
    ];

    public function messages()
    {
        return $this->embedsMany(Message::class);
    }

    public function user1()
    {
        return $this->belongsTo(Utilisateur::class, 'Ref_id_user1', '_id');
    }

    public function user2()
    {
        return $this->belongsTo(Utilisateur::class, 'Ref_id_user2', '_id');
    }
}
