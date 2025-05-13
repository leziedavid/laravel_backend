<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'libelle',
        'categorieTransactionsId',
        'sortie_caisse',
        'sortie_banque',
        'entree_caisse',
        'entree_banque',
        'type_operation',
        'details',
    ];

    // protected $casts = [
    //     'date' => 'date',
    //     'sortie_caisse' => 'decimal:2',
    //     'sortie_banque' => 'decimal:2',
    //     'entree_caisse' => 'decimal:2',
    //     'entree_banque' => 'decimal:2',
    // ];
}
