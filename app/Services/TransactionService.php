<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
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
                    // categorieTransactionsId
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
                                // Récupérer l’ID de la catégorie à partir du nom de la colonne "catégorie"
                                $categorieId = $this->getCategorieId($record['catégorie']);

                            Transaction::create([
                                'date' => $date, // Utiliser la date formatée
                                'libelle' => $record['Libelle'],
                                'sortie_caisse' => $sortieCaisse,
                                'sortie_banque' => $sortieBanque,
                                'entree_caisse' => $entreeCaisse,
                                'entree_banque' => $entreeBanque,
                                'type_operation' => $record['Type operation'] ?: null,
                                'details' => $record['Détails'] ?: null,
                                'categorieTransactionsId' => $categorieId,
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
    
    public function getCategorieTransaction()
    {
        // Récupérer les catégories de transactions triées par un champ (par exemple 'id') en ordre décroissant
        $categories = DB::table('categorie_transactions')->orderBy('id', 'desc')->get();
        // Retourner la réponse API avec les catégories
        return $this->apiResponse(200, "Consultation des catégories de transactions", $categories, 200);
    }
    

    public function getAlltransactions($filters)
    {
        // Récupérer les paramètres de filtrage
        $page = $filters['page']; //permet de faire la pagination
        $limit = $filters['limit']; //permet de faire la pagination
        $search = $filters['search']; //permet de faire la recherche sur une plage de date
        $category = $filters['category']; //permet de faire la recherche sur le type_operation
        $payment = $filters['payment']; //permet de faire la recherche sur le moyen de paiement
        $selectedYears = $filters['selectedYears']; //permet de faire la recherche sur l'année des transactions
        $selectedCategorie = $filters['selectedCategorie']; //permet de faire la recherche sur la catégorie des transactions

        // dd($category);
        // Construire la requête de base
        $query = DB::table('transactions')->orderBy('transactions.id', 'desc')->distinct();

        // Appliquer les filtres sur la plage de dates (si 'search' est défini)
        if (!empty($search) && strpos($search, ',') !== false) {
            // Séparer la plage de dates (search : '2025-01-22,2025-02-20')
            list($dateDeb, $dateFin) = explode(',', $search);

            // Vérifier que les deux dates sont valides
            if (strtotime($dateDeb) !== false && strtotime($dateFin) !== false) {
                $query->whereBetween('date', [$dateDeb, $dateFin]);
            }
        }

        // Appliquer le filtre par catégorie (type_operation)
        if (!empty($payment)) {
            $query->where('type_operation', '=', $payment);
        }

            // Appliquer le filtre par catégorie (si 'selectedCategorie' est défini)
            // ✅ Refactorisé avec une fonction privée
            if (!empty($selectedCategorie)) {
                $selectedCategorieId = $this->getCategorieId($selectedCategorie);
                if ($selectedCategorieId !== null) {
                    $query->where('categorieTransactionsId', '=', $selectedCategorieId);
                }
            }

        // Appliquer le filtre par moyen de paiement (sortie_caisse, sortie_banque, entree_caisse, entree_banque)
        if (!empty($category)) {
            // Le paiement peut concerner plusieurs colonnes, il faut donc vérifier chaque colonne
            if ($category === 'sortie_caisse') {
                $query->where('sortie_caisse', '>', 0); // Ex : "sortie_caisse" > 0
            } elseif ($category === 'sortie_banque') {
                $query->where('sortie_banque', '>', 0);
            } elseif ($category === 'entree_caisse') {
                $query->where('entree_caisse', '>', 0);
            } elseif ($category === 'entree_banque') {
                $query->where('entree_banque', '>', 0);
            }
        }

        // Appliquer le filtre par année (si 'selectedYears' est défini)
        if (!empty($selectedYears) && is_numeric($selectedYears)) {
            $query->whereYear('date', '=', $selectedYears);
        }

        // Ajouter la pagination en utilisant la méthode paginate de Laravel
        $orders = $query->paginate($limit, ['*'], 'page', $page);

        // Retourner la réponse API avec les commandes paginées
        return $this->apiResponse(200, "Consultation des commandes", $orders, 200);
    }


    public function getTransactionTotals($filters)
    {
        // Récupérer les paramètres de filtrage
        $page = $filters['page']; //permet de faire la pagination
        $limit = $filters['limit']; //permet de faire la pagination
        $search = $filters['search']; //permet de faire la recherche sur une plage de date
        $category = $filters['category']; //permet de faire la recherche sur le type_operation
        $payment = $filters['payment']; //permet de faire la recherche sur le moyen de paiement
        $selectedYears = $filters['selectedYears']; //permet de faire la recherche sur l'année des transactions
    
        // Construire la requête de base pour les totaux (pas besoin de pagination ici)
        $queryTotals = DB::table('transactions');
    
        // Appliquer les filtres sur la plage de dates (si 'search' est défini)
        if (!empty($search) && strpos($search, ',') !== false) {
            // Séparer la plage de dates (search : '2025-01-22,2025-02-20')
            list($dateDeb, $dateFin) = explode(',', $search);
    
            // Vérifier que les deux dates sont valides
            if (strtotime($dateDeb) !== false && strtotime($dateFin) !== false) {
                $queryTotals->whereBetween('date', [$dateDeb, $dateFin]);
            }
        }
    
            // Appliquer le filtre par catégorie (type_operation)
            if (!empty($payment)) {
                $queryTotals->where('type_operation', '=', $payment);
            }

        // ✅ Refactorisé avec une fonction privée
        if (!empty($selectedCategorie)) {
            $selectedCategorieId = $this->getCategorieId($selectedCategorie);
            if ($selectedCategorieId !== null) {
                $queryTotals->where('categorieTransactionsId', '=', $selectedCategorieId);
            }
        }
    
        // Appliquer le filtre par moyen de paiement (sortie_caisse, sortie_banque, entree_caisse, entree_banque)
        if (!empty($category)) {
            // Le paiement peut concerner plusieurs colonnes, il faut donc vérifier chaque colonne
            if ($category === 'sortie_caisse') {
                $queryTotals->where('sortie_caisse', '>', 0); // Ex : "sortie_caisse" > 0
            } elseif ($category === 'sortie_banque') {
                $queryTotals->where('sortie_banque', '>', 0);
            } elseif ($category === 'entree_caisse') {
                $queryTotals->where('entree_caisse', '>', 0);
            } elseif ($category === 'entree_banque') {
                $queryTotals->where('entree_banque', '>', 0);
            }
        }
    
        // Appliquer le filtre par année (si 'selectedYears' est défini)
        if (!empty($selectedYears) && is_numeric($selectedYears)) {
            $queryTotals->whereYear('date', '=', $selectedYears);
        }
    
        // Calcul des totaux pour chaque colonne sans `distinct`
        $totals = $queryTotals->selectRaw('
            SUM(sortie_caisse) as total_sortie_caisse,
            SUM(sortie_banque) as total_sortie_banque,
            SUM(entree_caisse) as total_entree_caisse,
            SUM(entree_banque) as total_entree_banque
        ')->first(); // Pas besoin de DISTINCT ici
    
        // Calculer le total général
        $totalGeneral = (
            $totals->total_sortie_caisse +
            $totals->total_sortie_banque +
            $totals->total_entree_caisse +
            $totals->total_entree_banque
        );
    
        // Construire la requête pour la pagination des transactions
        $queryTransactions = DB::table('transactions')->orderBy('transactions.id', 'desc');
    
        // Appliquer à nouveau les mêmes filtres pour la pagination
        if (!empty($search) && strpos($search, ',') !== false) {
            list($dateDeb, $dateFin) = explode(',', $search);
            if (strtotime($dateDeb) !== false && strtotime($dateFin) !== false) {
                $queryTransactions->whereBetween('date', [$dateDeb, $dateFin]);
            }
        }
    
        if (!empty($payment)) {
            $queryTransactions->where('type_operation', '=', $payment);
        }
    
        if (!empty($category)) {
            if ($category === 'sortie_caisse') {
                $queryTransactions->where('sortie_caisse', '>', 0);
            } elseif ($category === 'sortie_banque') {
                $queryTransactions->where('sortie_banque', '>', 0);
            } elseif ($category === 'entree_caisse') {
                $queryTransactions->where('entree_caisse', '>', 0);
            } elseif ($category === 'entree_banque') {
                $queryTransactions->where('entree_banque', '>', 0);
            }
        }
    
        if (!empty($selectedYears) && is_numeric($selectedYears)) {
            $queryTransactions->whereYear('date', '=', $selectedYears);
        }
    
        // Ajouter la pagination en utilisant la méthode paginate de Laravel
        // $orders = $queryTransactions->paginate($limit, ['*'], 'page', $page);
    
        // Retourner la réponse API avec les totaux et les commandes paginées
        return $this->apiResponse(200, "Consultation des commandes avec totaux", [
            'totals' => [
                'total_sortie_caisse' => floor($totals->total_sortie_caisse),
                'total_sortie_banque' => floor($totals->total_sortie_banque),
                'total_entree_caisse' => floor($totals->total_entree_caisse),
                'total_entree_banque' => floor($totals->total_entree_banque),
                'total_general' => floor($totalGeneral)
            ]
        ], 200);
        
    }



    // mes graphe

    
    public function getTransactionGraphs($filters)
{
    // Génère une couleur hexadécimale aléatoire
    $generateColor = function () {
        return sprintf("#%06X", mt_rand(0, 0xFFFFFF));
    };

    $query = DB::table('transactions');

    // 📌 Filtres
    if (!empty($filters['search']) && strpos($filters['search'], ',') !== false) {
        list($dateDeb, $dateFin) = explode(',', $filters['search']);
        if (strtotime($dateDeb) !== false && strtotime($dateFin) !== false) {
            $query->whereBetween('date', [$dateDeb, $dateFin]);
        }
    }

    if (!empty($filters['payment'])) {
        $query->where('type_operation', '=', $filters['payment']);
    }

        if (!empty($filters['selectedCategorie'])) {

            $selectedCategorieId = $this->getCategorieId($filters['selectedCategorie']);
            if ($selectedCategorieId !== null) {
                $query->where('categorieTransactionsId', '=', $selectedCategorieId);
            }
        }

    if (!empty($filters['category'])) {
        switch ($filters['category']) {
            case 'sortie_caisse':
                $query->where('sortie_caisse', '>', 0);
                break;
            case 'sortie_banque':
                $query->where('sortie_banque', '>', 0);
                break;
            case 'entree_caisse':
                $query->where('entree_caisse', '>', 0);
                break;
            case 'entree_banque':
                $query->where('entree_banque', '>', 0);
                break;
        }
    }

    if (!empty($filters['selectedYears']) && is_numeric($filters['selectedYears'])) {
        $query->whereYear('date', '=', $filters['selectedYears']);
    }

    $baseQuery = clone $query;
    $category = $filters['category'] ?? '';

    // 📊 1. BarGraphByDate (groupé par mois)
    $barByDate = (clone $baseQuery)
        ->select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
            DB::raw("MIN(date) as date"),
            DB::raw("
                SUM(
                    CASE
                        WHEN '{$category}' = '' THEN sortie_caisse + sortie_banque + entree_caisse + entree_banque
                        WHEN '{$category}' = 'sortie_caisse' THEN sortie_caisse
                        WHEN '{$category}' = 'sortie_banque' THEN sortie_banque
                        WHEN '{$category}' = 'entree_caisse' THEN entree_caisse
                        WHEN '{$category}' = 'entree_banque' THEN entree_banque
                        ELSE 0
                    END
                ) as total
            ")
        )
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $item->date, // brut pour clé unique
                'value' => (float)$item->total,
                'label' => 'Total mensuel',
                'color' => $generateColor(),
            ];
        });

    // 📊 2. BarGraphByTypeOperation
    $barByType = (clone $baseQuery)
        ->select(
            'type_operation',
            DB::raw("MIN(date) as date"),
            DB::raw("
                SUM(
                    CASE
                        WHEN '{$category}' = '' THEN sortie_caisse + sortie_banque + entree_caisse + entree_banque
                        WHEN '{$category}' = 'sortie_caisse' THEN sortie_caisse
                        WHEN '{$category}' = 'sortie_banque' THEN sortie_banque
                        WHEN '{$category}' = 'entree_caisse' THEN entree_caisse
                        WHEN '{$category}' = 'entree_banque' THEN entree_banque
                        ELSE 0
                    END
                ) as total
            ")
        )
        ->groupBy('type_operation')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $item->date, // brut
                'value' => (float)$item->total,
                'label' => $item->type_operation ?: 'Non défini',
                'color' => $generateColor(),
            ];
        });

    // 📊 3. BarGraphByCategorieTransactions
    $barByCategorie = (clone $baseQuery)
        ->join('categorie_transactions as c', 'transactions.categorieTransactionsId', '=', 'c.id')
        ->select(
            'c.label',
            DB::raw("MIN(transactions.date) as date"),
            DB::raw("
                SUM(
                    CASE
                        WHEN '{$category}' = '' THEN sortie_caisse + sortie_banque + entree_caisse + entree_banque
                        WHEN '{$category}' = 'sortie_caisse' THEN sortie_caisse
                        WHEN '{$category}' = 'sortie_banque' THEN sortie_banque
                        WHEN '{$category}' = 'entree_caisse' THEN entree_caisse
                        WHEN '{$category}' = 'entree_banque' THEN entree_banque
                        ELSE 0
                    END
                ) as total
            ")
        )
        ->groupBy('c.label')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $item->date,
                'value' => (float)$item->total,
                'label' => $item->label,
                'color' => $generateColor(),
            ];
        });

    // 🥧 4. PieGraphByDate
    $pieByDate = (clone $baseQuery)
        ->select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
            DB::raw("MIN(date) as date"),
            DB::raw("
                SUM(
                    CASE
                        WHEN '{$category}' = '' THEN sortie_caisse + sortie_banque + entree_caisse + entree_banque
                        WHEN '{$category}' = 'sortie_caisse' THEN sortie_caisse
                        WHEN '{$category}' = 'sortie_banque' THEN sortie_banque
                        WHEN '{$category}' = 'entree_caisse' THEN entree_caisse
                        WHEN '{$category}' = 'entree_banque' THEN entree_banque
                        ELSE 0
                    END
                ) as total
            ")
        )
        ->groupBy('month')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $item->date,
                'value' => (float)$item->total,
                'label' => 'Total par mois',
                'color' => $generateColor(),
            ];
        });

    // 🥧 5. PieGraphByTypeOperation
    $pieByType = (clone $baseQuery)
        ->select('type_operation',
            DB::raw("MIN(date) as date"),
            DB::raw("
                SUM(
                    CASE
                        WHEN '{$category}' = '' THEN sortie_caisse + sortie_banque + entree_caisse + entree_banque
                        WHEN '{$category}' = 'sortie_caisse' THEN sortie_caisse
                        WHEN '{$category}' = 'sortie_banque' THEN sortie_banque
                        WHEN '{$category}' = 'entree_caisse' THEN entree_caisse
                        WHEN '{$category}' = 'entree_banque' THEN entree_banque
                        ELSE 0
                    END
                ) as total
            ")
        )
        ->groupBy('type_operation')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $item->date,
                'value' => (float)$item->total,
                'label' => $item->type_operation ?: 'Non défini',
                'color' => $generateColor(),
            ];
        });

    // 🥧 6. PieGraphByCategorieTransactions
    $pieByCategorie = (clone $baseQuery)
        ->join('categorie_transactions as c', 'transactions.categorieTransactionsId', '=', 'c.id')
        ->select(
            'c.label',
            DB::raw("MIN(transactions.date) as date"),
            DB::raw("
                SUM(
                    CASE
                        WHEN '{$category}' = '' THEN sortie_caisse + sortie_banque + entree_caisse + entree_banque
                        WHEN '{$category}' = 'sortie_caisse' THEN sortie_caisse
                        WHEN '{$category}' = 'sortie_banque' THEN sortie_banque
                        WHEN '{$category}' = 'entree_caisse' THEN entree_caisse
                        WHEN '{$category}' = 'entree_banque' THEN entree_banque
                        ELSE 0
                    END
                ) as total
            ")
        )
        ->groupBy('c.label')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $item->date,
                'value' => (float)$item->total,
                'label' => $item->label,
                'color' => $generateColor(),
            ];
        });

    // ✅ Retour
    return $this->apiResponse(200, "Consultation des graphiques", [
        'BarGraphByDate' => $barByDate,
        'BarGraphByTypeOperation' => $barByType,
        'BarGraphByCategorieTransactions' => $barByCategorie,
        'PieGraphByDate' => $pieByDate,
        'PieGraphByTypeOperation' => $pieByType,
        'PieGraphByCategorieTransactions' => $pieByCategorie,
    ], 200);
}


public function getTransactionGraphs2($filters)
{
    // Fonction pour générer des couleurs aléatoires
    $generateColor = function () {
        return sprintf("#%06X", mt_rand(0, 0xFFFFFF));
    };

    $query = DB::table('transactions');

    // ✅ Application des filtres
    if (!empty($filters['search']) && strpos($filters['search'], ',') !== false) {
        list($dateDeb, $dateFin) = explode(',', $filters['search']);
        if (strtotime($dateDeb) !== false && strtotime($dateFin) !== false) {
            $query->whereBetween('date', [$dateDeb, $dateFin]);
        }
    }

    if (!empty($filters['payment'])) {
        $query->where('type_operation', '=', $filters['payment']);
    }

    if (!empty($filters['selectedCategorie'])) {
        $categorie = DB::table('categorie_transactions')->where('id', $filters['selectedCategorie'])->first();
        if ($categorie) {
            $query->where('categorieTransactionsId', '=', $categorie->id);
        }
    }

    if (!empty($filters['category'])) {
        switch ($filters['category']) {
            case 'sortie_caisse':
                $query->where('sortie_caisse', '>', 0);
                break;
            case 'sortie_banque':
                $query->where('sortie_banque', '>', 0);
                break;
            case 'entree_caisse':
                $query->where('entree_caisse', '>', 0);
                break;
            case 'entree_banque':
                $query->where('entree_banque', '>', 0);
                break;
        }
    }

    if (!empty($filters['selectedYears']) && is_numeric($filters['selectedYears'])) {
        $query->whereYear('date', '=', $filters['selectedYears']);
    }

    $baseQuery = clone $query;
    $category = $filters['category'] ?? '';

    // ✅ 1. BarGraphByDate (groupé par mois)
    $barByDate = (clone $baseQuery)
        ->select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
            DB::raw('MIN(date) as start_date'),
            DB::raw('MAX(date) as end_date'),
            DB::raw("
                SUM(
                    CASE
                        WHEN '{$category}' = '' THEN sortie_caisse + sortie_banque + entree_caisse + entree_banque
                        WHEN '{$category}' = 'sortie_caisse' THEN sortie_caisse
                        WHEN '{$category}' = 'sortie_banque' THEN sortie_banque
                        WHEN '{$category}' = 'entree_caisse' THEN entree_caisse
                        WHEN '{$category}' = 'entree_banque' THEN entree_banque
                        ELSE 0
                    END
                ) as total
            ")
        )
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $this->formatDateToLabel($item->start_date, $item->end_date),
                'value' => (float)$item->total,
                'label' => 'Total mensuel',
                'color' => $generateColor(),
            ];
        });

    // ✅ 2. BarGraphByTypeOperation
    $barByType = (clone $baseQuery)
        ->select('type_operation', DB::raw('
            MIN(date) as start_date,
            MAX(date) as end_date,
            SUM(
                CASE
                    WHEN "' . $category . '" = "" THEN sortie_caisse + sortie_banque + entree_caisse + entree_banque
                    WHEN "' . $category . '" = "sortie_caisse" THEN sortie_caisse
                    WHEN "' . $category . '" = "sortie_banque" THEN sortie_banque
                    WHEN "' . $category . '" = "entree_caisse" THEN entree_caisse
                    WHEN "' . $category . '" = "entree_banque" THEN entree_banque
                    ELSE 0
                END
            ) as total
        '))
        ->groupBy('type_operation')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $this->formatDateToLabel($item->start_date, $item->end_date),
                'value' => (float)$item->total,
                'label' => $item->type_operation ?: 'Non défini',
                'color' => $generateColor(),
            ];
        });

    // ✅ 3. BarGraphByCategorieTransactions
    $barByCategorie = (clone $baseQuery)
        ->join('categorie_transactions as c', 'transactions.categorieTransactionsId', '=', 'c.id')
        ->select('c.label', DB::raw('
            MIN(transactions.date) as start_date,
            MAX(transactions.date) as end_date,
            SUM(
                CASE
                    WHEN "' . $category . '" = "" THEN transactions.sortie_caisse + transactions.sortie_banque + transactions.entree_caisse + transactions.entree_banque
                    WHEN "' . $category . '" = "sortie_caisse" THEN transactions.sortie_caisse
                    WHEN "' . $category . '" = "sortie_banque" THEN transactions.sortie_banque
                    WHEN "' . $category . '" = "entree_caisse" THEN transactions.entree_caisse
                    WHEN "' . $category . '" = "entree_banque" THEN transactions.entree_banque
                    ELSE 0
                END
            ) as total
        '))
        ->groupBy('c.label')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $this->formatDateToLabel($item->start_date, $item->end_date),
                'value' => (float)$item->total,
                'label' => $item->label,
                'color' => $generateColor(),
            ];
        });

    // ✅ 4. PieGraphByDate (graphique circulaire basé sur les mois)
    $pieByDate = (clone $baseQuery)
        ->select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
            DB::raw("
                SUM(
                    CASE
                        WHEN '{$category}' = '' THEN sortie_caisse + sortie_banque + entree_caisse + entree_banque
                        WHEN '{$category}' = 'sortie_caisse' THEN sortie_caisse
                        WHEN '{$category}' = 'sortie_banque' THEN sortie_banque
                        WHEN '{$category}' = 'entree_caisse' THEN entree_caisse
                        WHEN '{$category}' = 'entree_banque' THEN entree_banque
                        ELSE 0
                    END
                ) as total
            ")
        )
        ->groupBy('month')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $this->formatDateToLabel($item->month . '-01', $item->month . '-31'), // Format le mois pour l'affichage
                'value' => (float)$item->total,
                'label' => 'Total par mois',
                'color' => $generateColor(),
            ];
        });

    // ✅ 5. PieGraphByTypeOperation (graphique circulaire basé sur les types d'opération)
    $pieByType = (clone $baseQuery)
        ->select('type_operation', DB::raw('
            SUM(
                CASE
                    WHEN "' . $category . '" = "" THEN sortie_caisse + sortie_banque + entree_caisse + entree_banque
                    WHEN "' . $category . '" = "sortie_caisse" THEN sortie_caisse
                    WHEN "' . $category . '" = "sortie_banque" THEN sortie_banque
                    WHEN "' . $category . '" = "entree_caisse" THEN entree_caisse
                    WHEN "' . $category . '" = "entree_banque" THEN entree_banque
                    ELSE 0
                END
            ) as total
        '))
        ->groupBy('type_operation')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $item->type_operation ?: 'Non défini',
                'value' => (float)$item->total,
                'label' => $item->type_operation ?: 'Non défini',
                'color' => $generateColor(),
            ];
        });

    // ✅ 6. PieGraphByCategorieTransactions (graphique circulaire basé sur les catégories de transactions)
    $pieByCategorie = (clone $baseQuery)
        ->join('categorie_transactions as c', 'transactions.categorieTransactionsId', '=', 'c.id')
        ->select('c.label', DB::raw('
            SUM(
                CASE
                    WHEN "' . $category . '" = "" THEN transactions.sortie_caisse + transactions.sortie_banque + transactions.entree_caisse + transactions.entree_banque
                    WHEN "' . $category . '" = "sortie_caisse" THEN transactions.sortie_caisse
                    WHEN "' . $category . '" = "sortie_banque" THEN transactions.sortie_banque
                    WHEN "' . $category . '" = "entree_caisse" THEN transactions.entree_caisse
                    WHEN "' . $category . '" = "entree_banque" THEN transactions.entree_banque
                    ELSE 0
                END
            ) as total
        '))
        ->groupBy('c.label')
        ->get()
        ->map(function ($item) use ($generateColor) {
            return [
                'date' => $item->label,
                'value' => (float)$item->total,
                'label' => $item->label,
                'color' => $generateColor(),
            ];
        });

    return $this->apiResponse(200, "Consultation des graphiques", [
        'BarGraphByDate' => $barByDate,
        'BarGraphByTypeOperation' => $barByType,
        'BarGraphByCategorieTransactions' => $barByCategorie,
        'PieGraphByDate' => $pieByDate,
        'PieGraphByTypeOperation' => $pieByType,
        'PieGraphByCategorieTransactions' => $pieByCategorie,
    ], 200);
}

    
    
    /**
     * Formate une plage de dates en "01 Janvier 2025 - 31 Janvier 2025"
     */
    private function formatDateToLabel($start, $end)
    {
        try {
            Carbon::setLocale('fr');
    
            $formatMonth = function ($date) {
                $month = Carbon::parse($date)->translatedFormat('F'); // ex : janvier
                $month = mb_strtolower($month); // on force en minuscule (utile pour Février → février)
    
                return mb_strlen($month) > 5 ? mb_substr($month, 0, 5) : $month;
            };
    
            $startFormatted = Carbon::parse($start)->format('d') . ' ' . $formatMonth($start) . ' ' . Carbon::parse($start)->format('Y');
            $endFormatted = Carbon::parse($end)->format('d') . ' ' . $formatMonth($end) . ' ' . Carbon::parse($end)->format('Y');
    
            return "{$startFormatted} - {$endFormatted}";
        } catch (\Exception $e) {
            return $start . ' - ' . $end;
        }
    }


    // 🔒 Fonction privée pour récupérer l'ID d'une catégorie
    private function getCategorieId($categorieInput)
    {
        // On suppose que $categorieInput est un ID, mais on pourrait étendre pour accepter aussi le nom
        $categorie = DB::table('categorie_transactions')
            ->where('id', $categorieInput)
            ->orWhere('label', $categorieInput)
            ->first();

        return $categorie ? $categorie->id : null;
    }


    public function saveTransactions(Request $request)
    {
        try {
            // On récupère les données du formulaire
            $data = $request->all();

            // Initialisation des colonnes
            $entreeCaisse = 0;
            $entreeBanque = 0;
            $sortieCaisse = 0;
            $sortieBanque = 0;

            // On détermine quelle colonne remplir
            switch ($data['typeTransaction']) {
                case 'entree_caisse':
                    $entreeCaisse = (float) $data['somme'];
                    break;
                case 'entree_banque':
                    $entreeBanque = (float) $data['somme'];
                    break;
                case 'sortie_caisse':
                    $sortieCaisse = (float) $data['somme'];
                    break;
                case 'sortie_banque':
                    $sortieBanque = (float) $data['somme'];
                    break;
                default:
                    return $this->apiResponse(400, "Type de transaction invalide", [], 400);
            }

            // Création de la transaction
            $transaction = Transaction::create([
                'date' => $data['date'],
                'libelle' => $data['libelle'],
                'categorieTransactionsId' => (int) $data['categorieTransactionsId'],
                // 'categorieTransactionsId' => $data['categorieTransactionsId'],
                'sortie_caisse' => $sortieCaisse,
                'sortie_banque' => $sortieBanque,
                'entree_caisse' => $entreeCaisse,
                'entree_banque' => $entreeBanque,
                'type_operation' => $data['type_operation'] ?? null,
                'details' => $data['details'] ?? null,
            ]);

            return $this->apiResponse(201, "Transaction enregistrée avec succès.", $transaction, 201);
        } catch (\Exception $e) {
            return $this->apiResponse(500, "Erreur serveur", ['message' => $e->getMessage()], 500);
        }
    }



}
