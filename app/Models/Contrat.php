<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Contrat extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'contrats';

    protected $fillable=[
        'typeContrat',
        'dateSignature',
        'Ref_id_paiement',
    ];
    protected $dates=['dateSignature'];
    
    public function paiement(){
        return $this->belongsTo(Paiement::class,'Ref_id_paiement');
    }
}
