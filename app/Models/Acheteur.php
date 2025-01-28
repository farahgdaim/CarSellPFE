<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;


class Acheteur extends Model
{
    protected $connection = 'mongodb';
    protected $collection ='acheteurs';
    use HasFactory;
    protected $fillable = [
       
    ];
    public function user(){
        return $this->belongsTo(Utilisateur::class);
    }
}
