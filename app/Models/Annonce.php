<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Annonce extends Model
{

    use HasFactory;
    protected $connection = 'mongodb'; // Utilisation de MongoDB
    protected $collection = 'annonces'; // Nom de la collection MongoDB

    protected $fillable = [
        'titre', 'description', 'prix', 'status',
        'supervisée_par', 'Ref_id_user', 'vehicule', 'images','reported_by','is_reported'
    ];
        
    public function vehicule()
    {
        return $this->embedsOne(Vehicule::class);
    }

    
    public function images()
    {
        return $this->embedsMany(Image::class);
    }
    public function user(){
        return $this->belongsTo(Utilisateur::class, 'Ref_id_user','_id');
    }

    public function admin(){
        return $this->belongsTo(Admin::class, 'supervisée_par','_id');
    }


    public function sponsorings()
    {
        return $this->hasMany(Sponsoring::class, 'id_annonce', '_id');

    }
    public function sponsorships()
    {
        return $this->hasMany(Sponsorship::class, 'id_annonce');
    }

   /*  protected $casts = [
    
    'images' => 'array'
]; */
}
