<?php


namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistiquesController extends Controller
{
    public function getStatistiques()
    {

        // Nombre total d'annonces
        $totalAnnonces = Annonce::count();

        // Nombre total d'annonces vendues
        $annoncesVendues = Annonce::where('status', 'vendue')->count();
        $prixTotal = Annonce::sum('prix');
        $prixMoyen = Annonce::avg('prix');

        $prixMax = Annonce::orderBy('prix', 'desc')->value('prix');
        $prixMin = Annonce::orderBy('prix', 'asc')->value('prix');
        
        $annoncesVenduesParMarque = Annonce::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['status' => 'vendue']],
                ['$group' => ['_id' => '$vehicule.Marque', 'total' => ['$sum' => 1]]],
            ]);
        });
        // Nombre total des utilisateurs
        $totalUsers = Utilisateur::count();

        // Nombre d'annonces par marque (pour le diagramme en bâtons) avec agrégation MongoDB
        $annoncesParMarque = Annonce::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => '$vehicule.Marque',
                    'total' => ['$sum' => 1]
                ]],
                ['$project' => [
                    'marque' => '$_id',
                    'total' => 1,
                    '_id' => 0
                ]]
            ]);
        });
        $kilometrageMoyenParMarque = Annonce::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => ['_id' => '$vehicule.Marque', 'moyenKm' => ['$avg' => '$vehicule.Kilométrage']]],
            ]);
        });

        // Nombre d'annonces vendues par marque avec agrégation MongoDB
        $annoncesVenduesParMarque = Annonce::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['status' => 'vendue']],
                ['$group' => [
                    '_id' => '$vehicule.Marque',
                    'total' => ['$sum' => 1]
                ]],
                ['$project' => [
                    'marque' => '$_id',
                    'total' => 1,
                    '_id' => 0
                ]]
            ]);
        });
        $annoncesParMois = Annonce::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => [
                            '$dateToString' => ['format' => '%Y-%m', 'date' => '$created_at'],
                        ],
                        'total' => ['$sum' => 1],
                    ],
                ],
                ['$sort' => ['_id' => 1]],
            ]);
        });
        $topUtilisateurs = Annonce::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => ['$toObjectId' => '$Ref_id_user'],
                        'totalAnnonces' => ['$sum' => 1],
                    ],
                ],
                [
                    '$sort' => ['totalAnnonces' => -1]
                ],
                [
                    '$limit' => 5
                ],
                [
                    '$lookup' => [
                        'from' => 'utilisateurs',
                        'localField' => '_id',
                        'foreignField' => '_id',
                        'as' => 'utilisateur'
                    ]
                ],
                [
                    '$unwind' => '$utilisateur'
                ],
                [
                    '$project' => [
                        'nom' => '$utilisateur.nom',
                        'totalAnnonces' => 1
                    ]
                ]
            ]);
        });
        
        


        // Nombre d'annonces vendues par mois avec agrégation MongoDB
        $annoncesVenduesParMois = Annonce::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['status' => 'vendue']],
                ['$group' => [
                    '_id' => [
                        'year' => ['$year' => '$updated_at'],
                        'month' => ['$month' => '$updated_at']
                    ],
                    'total' => ['$sum' => 1]
                ]],
                ['$sort' => ['_id.year' => 1, '_id.month' => 1]],
                ['$project' => [
                    'mois' => [
                        '$concat' => [
                            ['$toString' => '$_id.year'],
                            '-',
                            ['$toString' => '$_id.month']
                        ]
                    ],
                    'total' => 1,
                    '_id' => 0
                ]]
            ]);
        });

        $annoncesPublieeParMois = Annonce::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => [
                        'year' => ['$year' => '$created_at'],
                        'month' => ['$month' => '$created_at']
                    ],
                    'total' => ['$sum' => 1]
                ]],
                ['$sort' => ['_id.year' => 1, '_id.month' => 1]],
                ['$project' => [
                    'mois' => [
                        '$concat' => [
                            ['$toString' => '$_id.year'],
                            '-',
                            ['$toString' => '$_id.month']
                        ]
                    ],
                    'total' => 1,
                    '_id' => 0
                ]]
            ]);
        });
        // Prix total des annonces vendues
        $prixTotalVentes = Annonce::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['status' => 'vendue']],
                ['$group' => [
                    '_id' => null,
                    'totalPrix' => ['$sum' => '$prix']
                ]]
            ]);
        });
        



        return response()->json([
            'totalAnnonces' => $totalAnnonces,
            'annoncesVendues' => $annoncesVendues,
            'totalUsers' => $totalUsers,
            'annoncesParMarque' => $annoncesParMarque,
            'annoncesVenduesParMarque' => $annoncesVenduesParMarque,
            'annoncesVenduesParMois' => $annoncesVenduesParMois,
            'annoncesPublieeParMois' => $annoncesPublieeParMois,
            'prixMoyen' => $prixMoyen,
            'prixTotal' => $prixTotal,
            'prixMax' => $prixMax,
            'prixMin' => $prixMin,
            'prixTotalVentes' => $prixTotalVentes->toArray()[0]['totalPrix'] ?? 0,
            'topUtilisateurs' => $topUtilisateurs,
            'annoncesParMois' => $annoncesParMois,
            'kilometrageMoyenParMarque' => $kilometrageMoyenParMarque,
        ]);
    }
}
