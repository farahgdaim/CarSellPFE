<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $fillable = [
        'montant', 'datePaiement','statusPaiement'
    ];
    public function contrats()
{
    return $this->hasMany(Contrat::class, '_id');
}

}
