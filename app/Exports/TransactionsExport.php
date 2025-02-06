<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    /**
     * Constructeur pour recevoir les filtres.
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Vérifie si la collection est vide.
     */
    public function isEmpty(): bool
    {
        return $this->query()->count() === 0;
    }

    /**
     * Requête filtrée.
     */
    protected function query()
    {
        $filters = $this->filters;

        // Requête de base
        $query = DB::table('transactions');

        // Appliquer les filtres
        if (!empty($filters['search']) && strpos($filters['search'], ',') !== false) {
            list($dateDeb, $dateFin) = explode(',', $filters['search']);
            if (strtotime($dateDeb) !== false && strtotime($dateFin) !== false) {
                $query->whereBetween('date', [$dateDeb, $dateFin]);
            }
        }

        if (!empty($filters['payment'])) {
            $query->where('type_operation', '=', $filters['payment']);
        }

        if (!empty($filters['category'])) {
            if ($filters['category'] === 'sortie_caisse') {
                $query->where('sortie_caisse', '>', 0);
            } elseif ($filters['category'] === 'sortie_banque') {
                $query->where('sortie_banque', '>', 0);
            } elseif ($filters['category'] === 'entree_caisse') {
                $query->where('entree_caisse', '>', 0);
            } elseif ($filters['category'] === 'entree_banque') {
                $query->where('entree_banque', '>', 0);
            }
        }

        if (!empty($filters['selectedYears']) && is_numeric($filters['selectedYears'])) {
            $query->whereYear('date', '=', $filters['selectedYears']);
        }

        // // Appliquer la pagination
        // if (!empty($filters['page']) && !empty($filters['limit'])) {
        //     $offset = ($filters['page'] - 1) * $filters['limit'];
        //     $query->offset($offset)->limit($filters['limit']);
        // }

        return $query;
    }

    /**
     * Récupérer les données filtrées.
     */
    public function collection()
    {
        return $this->query()->get();
    }

    /**
     * Définir les en-têtes des colonnes.
     */
    public function headings(): array
    {
        return [
            'DATE',
            'LIBELLE',
            'SORTIE_CAISSE',
            'SORTIE_BANQUE',
            'ENTREE_CAISSE',
            'ENTREE_BANQUE',
            'TYPE_OPERATION',
            'DETAILS',
        ];
    }

    /**
     * Mapper les données pour chaque ligne.
     */
    public function map($transaction): array
    {
        return [
            $transaction->date,
            $transaction->libelle,
            $transaction->sortie_caisse ?: 0,
            $transaction->sortie_banque ?: 0,
            $transaction->entree_caisse ?: 0,
            $transaction->entree_banque ?: 0,
            strtoupper(str_replace(' ', '_', $transaction->type_operation)),
            $transaction->details,
        ];
    }
}
