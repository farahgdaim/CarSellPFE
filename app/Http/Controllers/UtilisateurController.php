<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expert;
use App\Models\DemandeEvaluation;
use Illuminate\Support\Facades\Log;

class UtilisateurController extends Controller
{
    /**
     * L'utilisateur connecté fait une demande pour devenir expert
     * en fournissant son fichier PDF de certification, son domaine d'expertise et ses années d'expérience.
     */
    public function requestExpertRole(Request $request)
    {
        $user = auth()->user();
        Log::info("UtilisateurController: requestExpertRole called.");
        Log::info("User authenticated: " . json_encode($user));

        // Vérifier si une demande existe déjà pour cet utilisateur
        $existingExpert = Expert::where('ref_id_utilisateur', $user->_id)->first();
        if ($existingExpert) {
            return response()->json(['message' => 'Une demande est déjà en cours ou vous êtes déjà expert.'], 400);
        }

        $validatedData = $request->validate([
            'certification'    => 'required|file|mimes:pdf|max:2048',
            'domaineExpertise' => 'required|string',
            'anneesExperience' => 'required|integer|min:0'
        ]);
        Log::info("Request validated: " . json_encode($validatedData));

        $file = $request->file('certification');
        Log::info("Certification file received: " . $file->getClientOriginalName());
        $path = $file->store('certifications', 'public');
        Log::info("File stored at path: " . $path);

        $expert = Expert::create([
            'ref_id_utilisateur' => $user->_id,
            'certifications'     => [$path],
            'domaineExpertise'   => $request->input('domaineExpertise'),
            'anneesExperience'   => $request->input('anneesExperience'),
            'status'             => 'pending'
        ]);
        Log::info("Expert record created: " . json_encode($expert));

        return response()->json([
            'message' => 'Demande pour devenir expert envoyée avec succès.',
            'expert'  => $expert
        ]);
    }

    /**
     * Créer une demande d'évaluation.
     * Utilisé par l'utilisateur qui demande une évaluation.
     */
    public function requestEvaluation(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'ref_id_annonce' => 'required|string',
            'ref_id_expert'  => 'required|string',
        ]);

        $demande = DemandeEvaluation::create([
            'ref_id_annonce'   => $request->input('ref_id_annonce'),
            'ref_id_demandeur' => $user->_id,
            'ref_id_expert'    => $request->input('ref_id_expert'),
            'status'           => 'pending'
        ]);

        return response()->json([
            'message' => 'Demande d’évaluation créée avec succès.',
            'demande' => $demande
        ]);
    }
}
