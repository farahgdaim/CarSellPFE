<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class sponsorship extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'sponsorships';

    protected $fillable = [
        'DateDebut', 
        'DateFin',
        'id_annonce',
        'id_sponsoring'
    ];

    public function annonce()
    {
        return $this->belongsTo(Annonce::class, 'AnnonceId');
    }

    public function sponsoring()
    {
        return $this->belongsTo(Sponsoring::class, 'SponsoringId');
    }
    
}
