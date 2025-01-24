<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TransactionService
{

    use ApiResponse;

    public function importTransactions(Request $request)
    {
        try {
            // Vérifier si des fichiers sont envoyés
            if (!$request->hasFile('files')) {
                return $this->apiResponse(400, "Aucun fichier n'a été envoyé.", [], 400);
            }
    
            // Récupérer les fichiers envoyés
            $files = $request->file('files');
    
            // Si un seul fichier est envoyé, le convertir en tableau
            if (!is_array($files)) {
                $files = [$files];  // Ici, on transforme le fichier unique en tableau pour éviter les erreurs lors du traitement
            }
    
            $totalImportedCount = 0; // Compteur global pour tous les fichiers
    
            // Parcourir chaque fichier envoyé
            foreach ($files as $file) {
                // Vérifier si le fichier est valide
                if (!$file || !$file->isValid()) {
                    return $this->apiResponse(400, "Un fichier n'est pas valide.", [], 400);
                }
    
                // Charger les données du fichier CSV
                $csv = Reader::createFromPath($file->getRealPath());
                $csv->setHeaderOffset(0);
                $records = iterator_to_array($csv->getRecords());
    
                // Trier les enregistrements par date (ascendant)
                usort($records, function ($a, $b) {
                    $dateA = \Carbon\Carbon::createFromFormat('d/m/Y', $a['Date'])->timestamp;
                    $dateB = \Carbon\Carbon::createFromFormat('d/m/Y', $b['Date'])->timestamp;
                    return $dateA <=> $dateB;
                });


                // Encoder les enregistrements après le tri pour les caractères spéciaux
                foreach ($records as &$record) {
                    // Décoder les entités Unicode (par exemple "\u00c0" devient "À")
                    $record['Libelle'] = json_decode('"' . $record['Libelle'] . '"');
                    $record['Type operation'] = json_decode('"' . $record['Type operation'] . '"');
                    $record['Détails'] = json_decode('"' . $record['Détails'] . '"');

                    // Optionnel : nettoyer les entités Unicode restantes
                    $record['Libelle'] = mb_convert_encoding($record['Libelle'], 'UTF-8', 'auto');
                    $record['Type operation'] = mb_convert_encoding($record['Type operation'], 'UTF-8', 'auto');
                    $record['Détails'] = mb_convert_encoding($record['Détails'], 'UTF-8', 'auto');
                }

                // Récupérer la date du dernier enregistrement dans la base de données
                $lastTransaction = Transaction::orderBy('date', 'desc')->first();
                $lastDate = $lastTransaction ? $lastTransaction->date : null;
    
                $importedCount = 0;
    
                // Commencer une transaction pour l'import
                DB::transaction(function () use ($records, $lastDate, &$importedCount) {
                    foreach ($records as $record) {
                        // Formater correctement la date pour qu'elle soit au format YYYY-MM-DD
                        $dateString = str_replace('\/', '/', $record['Date']);
                        $date = \Carbon\Carbon::createFromFormat('d/m/Y', $dateString)->toDateString(); // => Format YYYY-MM-DD
                
                        // Remplacer les valeurs nulles ou vides par 0 pour les champs numériques
                        $sortieCaisse = empty($record['Sortie caisse']) ? 0 : (float)$record['Sortie caisse'];
                        $sortieBanque = empty($record['Sortie banque']) ? 0 : (float)$record['Sortie banque'];
                        $entreeCaisse = empty($record['Entrée caisse']) ? 0 : (float)$record['Entrée caisse'];
                        $entreeBanque = empty($record['Entrée banque']) ? 0 : (float)$record['Entrée banque'];
                
                        // Vérifier si la date est supérieure ou égale à la dernière date enregistrée
                        if (is_null($lastDate) || $date > $lastDate) {
                            // Créer une nouvelle transaction
                            Transaction::create([
                                'date' => $date, // Utiliser la date formatée
                                'libelle' => $record['Libelle'],
                                'sortie_caisse' => $sortieCaisse,
                                'sortie_banque' => $sortieBanque,
                                'entree_caisse' => $entreeCaisse,
                                'entree_banque' => $entreeBanque,
                                'type_operation' => $record['Type operation'] ?: null,
                                'details' => $record['Détails'] ?: null,
                            ]);
                
                            $importedCount++;
                        }
                    }
                });
                
    
                // Additionner le nombre de transactions importées pour ce fichier
                $totalImportedCount += $importedCount;
            }
    
            // Retourner la réponse avec le nombre total de transactions importées
            return $this->apiResponse(200, "Importation terminée.", ['imported_count' => $totalImportedCount], 200);
    
        } catch (\Exception $e) {
            // Gestion des erreurs
            return $this->apiResponse(500, "Une erreur est survenue lors de l'importation.", $records, 500);
        }
    }
    

    public function getAlltransactions($filters)
    {
        // Récupérer les paramètres de filtrage
        $page = $filters['page'];
        $limit = $filters['limit'];
        $search = $filters['search'];
        // Construire la requête de base
        $query = DB::table('transactions')->orderBy('transactions.id', 'desc')->distinct();
        // Ajouter un filtre de recherche sur le champ 'transaction_id' si un terme de recherche est fourni
        // if ($search) {
        //     $query->where('transactions.date', 'like', '%' . $search . '%');
        // }
        // Ajouter la pagination en utilisant la méthode paginate de Laravel
        $orders = $query->paginate($limit, ['*'], 'page', $page);
        // Retourner la réponse API avec les commandes paginées
        return $this->apiResponse(200, "Consultation des commandes", $orders, 200);
    }


}
