<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
//use Jenssegers\Mongodb\Eloquent\Model;
class Conversation extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'conversations';

    protected $fillable = [
        'contenu',
        'messages',
        'Ref_id_user'
        
    ];
    protected $attributes = [
        'messages' => []  // Pour éviter les erreurs quand il est null
    ];

    public function messages(){
        return $this->embedsMany(Message::class);
    }

    public function user(){
        return $this->belongsTo(Utilisateur::class, 'Ref_id_user','_id');
    }
}
