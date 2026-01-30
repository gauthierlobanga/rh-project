<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'echeance_id',
        'emprunt_id',
        'montant_paye',
        'date_paiement',
        'mode_paiement',
        'reference_paiement',
        'est_partiel',
        'montant_restant',
        'notes',
    ];

    public function echeance()
    {
        return $this->belongsTo(Echeance::class);
    }

    public function emprunt()
    {
        return $this->belongsTo(Emprunt::class);
    }
}
