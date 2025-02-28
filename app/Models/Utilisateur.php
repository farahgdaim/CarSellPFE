<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Classe Utilisateur
 * Représente un utilisateur avec ses attributs et ses sous-documents.
 */
class Utilisateur extends Model implements JWTSubject, AuthenticatableContract
{
    use Authenticatable;

    // On précise qu'on utilise la connexion MongoDB
    protected $connection = 'mongodb';

    // Nom de la collection dans la base
    protected $collection = 'utilisateurs';

    /**
     * Les champs (et sous-documents) qu'on peut remplir.
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'dateInscription',
        'notifications',
        'paiements',
        // Nouveaux attributs pour la demande d'expertise
        'role',
        'expertRequested',
        'certifications',
        'domaineExpertise',
        'anneesExperience'
    ];
    

    /**
     * Champs à masquer dans les retours JSON (ex: le mot de passe).
     */
    protected $hidden = [
        'password'
    ];

    // Valeurs par défaut
    protected $attributes = [
        'role'            => 'user',   // par défaut, simple utilisateur
        'expertRequested' => false     // par défaut, pas de demande d'expertise
    ];

    /**
     * Ajoute une notification dans le tableau notifications.
     *
     * @param  string $contenu   Contenu du message
     * @param  string $statut    Statut de la notification (ex: "non_lu", "lu")
     */
    public function addNotification(string $contenu, string $statut = 'non_lu')
    {
        $notification = [
            'contenu' => $contenu,
            'date'    => now(),
            'statut'  => $statut,
        ];

        // push() permet d'ajouter un élément dans un tableau MongoDB
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
