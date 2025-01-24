<?php

namespace App\Http\Controllers\Api;

use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;


class TransactionController extends Controller
{

    use ApiResponse;

    
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Importer des transactions depuis un ou plusieurs fichiers CSV.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        // Validation de la requête pour s'assurer que les fichiers sont bien envoyés
        // $request->validate([
        //     'files' => 'required|array', // "files" doit être un tableau
        //     'files.*' => 'file|mimes:csv,txt|max:2048', // Chaque fichier doit être un CSV ou TXT et respecter une taille max
        // ]);

        try {
            // Appel du service pour gérer l'importation et retour direct de la réponse
            return $this->transactionService->importTransactions($request);
        } catch (\Exception $e) {
            // Gestion des erreurs inattendues
            return $this->apiResponse(500, "Une erreur est survenue lors de l'importation.", $e->getMessage(), 500);
        }
    }


    public function alltransactions(Request $request)
    {
        // Récupérer les paramètres de la requête (page, limit, search)
        $filters = [
            'page' => $request->query('page', 1),   // Par défaut, la page est 1
            'limit' => $request->query('limit', 10), // Par défaut, la limite est 10
            'search' => $request->query('search', null), // Si 'search' est fourni, on l'utilise, sinon null
        ];
        // Passer les filtres au service pour récupérer les données
        return $this->transactionService->getAlltransactions($filters);
    }

}
