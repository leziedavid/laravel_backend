<?php

namespace App\Http\Controllers\Api;

use App\Exports\TransactionsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\ApiResponse;

class ExportController extends Controller
{

    use ApiResponse;

    /**
     * Exporter les transactions en Excel avec filtres.
     */

    public function exportTransactions(Request $request)
    {
        // Récupérer les paramètres de la requête
        $filters = [
            'page' => $request->query('page', 1),
            'limit' => $request->query('limit', 10),
            'search' => $request->query('search', null),
            'category' => $request->query('category', null),
            'payment' => $request->query('payment', null),
            'selectedYears' => $request->query('selectedYears', null),
        ];

        // Créer une instance de TransactionsExport
        $export = new TransactionsExport($filters);

        // Vérifier si la collection est vide
        if ($export->isEmpty()) {
            return $this->apiResponse(400, "Erreur : aucun résultat trouvé pour les filtres fournis.", [], 400);
        }

        // Définir le nom et le chemin du fichier
        $fileName = 'Export_' . time() . '.xlsx';
        $filePath = public_path('Export/' . $fileName);

        // Créer le répertoire "Export" s'il n'existe pas
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Obtenir le contenu du fichier Excel
        $excelContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        // Sauvegarder le contenu dans le répertoire public
        file_put_contents($filePath, $excelContent);

        // Construire l'URL publique du fichier
        $fileUrl = asset('Export/' . $fileName);

        // Retourner la réponse avec l'URL du fichier
        return $this->apiResponse(200, "Exportation réussie.", ['url' => $fileUrl], 200);
    }
    
    public function exportTransactions1(Request $request)
    {
        // Récupérer les paramètres de la requête
        $filters = [
            'page' => $request->query('page', 1),
            'limit' => $request->query('limit', 10),
            'search' => $request->query('search', null),
            'category' => $request->query('category', null),
            'payment' => $request->query('payment', null),
            'selectedYears' => $request->query('selectedYears', null),
        ];

        // Créer une instance de TransactionsExport
        $export = new TransactionsExport($filters);

        // Vérifier si la collection est vide
        if ($export->isEmpty()) {
            return response()->json(['status' => 400,'message' => "Erreur : aucun résultat trouvé pour les filtres fournis.",
                'data' => [],], 400);
        }

        // Procéder à l'export si des résultats sont trouvés
        return Excel::download($export, 'transactions_ministere.xlsx');
    }
}
