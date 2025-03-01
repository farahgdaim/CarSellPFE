<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Utilisateur extends Model implements JWTSubject, AuthenticatableContract
{
    use Authenticatable;

    protected $connection = 'mongodb';
    protected $collection = 'utilisateurs';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'dateInscription',
        'notifications',
        'paiements',
        // Facultatif : référence à l'admin (exemple d'usage)
        'ref_id_admin'
    ];

    protected $hidden = [
        'password'
    ];

    // Exemple de méthode pour ajouter une notification avec référence à l'utilisateur
    public function addNotification(string $contenu, string $statut = 'non_lu')
    {
        $notification = [
            'ref_id_user' => $this->_id, // référence à cet utilisateur
            'contenu'     => $contenu,
            'date'        => now(),
            'statut'      => $statut,
        ];

        $this->push('notifications', $notification);
    }

    /**
     * Marque une notification comme lue.
     *
     * @param  int  $index  Index de la notification dans le tableau
     */
    public function markNotificationAsRead(int $index)
    {
        // Récupérer le tableau complet des notifications
        $notifications = $this->notifications ?? [];

        // Vérifier si l'index existe dans le tableau
        if (isset($notifications[$index])) {
            // Modifier la notification localement
            $notifications[$index]['statut'] = 'lu';

            // Réassigner le tableau modifié à la propriété notifications
            $this->notifications = $notifications;
            $this->save();
        }
    }

    /**
     * Ajoute un paiement dans le tableau paiements.
     *
     * @param  float  $montant         Montant du paiement
     * @param  string $statutPaiement  Statut du paiement (ex: "en_attente", "validé", "refusé")
     */
    public function addPaiement(float $montant, string $statutPaiement = 'en_attente')
    {
        $paiement = [
            
            'ref_id_paying' => $this->_id, 
            //'ref_id_receiving' => not sure yet, 
            'montant'         => $montant,
            'date'            => now(),
            'statutPaiement'  => $statutPaiement
        ];

        $this->push('paiements', $paiement);
    }

    /**
     * Met à jour le statut d'un paiement.
     *
     * @param  int    $index
     * @param  string $statutPaiement
     */
    public function updatePaiementStatus(int $index, string $statutPaiement)
    {
        // Récupérer le tableau complet des paiements
        $paiements = $this->paiements ?? [];

        // Vérifier si l'index existe dans le tableau
        if (isset($paiements[$index])) {
            // Modifier le paiement localement
            $paiements[$index]['statutPaiement'] = $statutPaiement;

            // Réassigner le tableau modifié à la propriété paiements
            $this->paiements = $paiements;
            $this->save();
        }
    }

    // ================== JWT Auth Requirements ==================

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
