<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Jenssegers\Mongodb\Eloquent\Model;
//use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Model;

class Expert extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection ='experts';


    protected $fillable = [
        'specialisation'
    ];
}
