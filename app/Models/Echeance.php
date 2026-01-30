<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Echeance extends Model
{
    use HasFactory;

    protected $fillable = [
        'emprunt_id',
        'numero_echeance',
        'date_echeance',
        'est_payee',
        'date_paiement',
        'capital_initial',
        'montant_echeance',
        'part_interets',
        'part_capital',
        'capital_restant',
        'interets_cumules',
        'capital_cumule',
    ];

    protected $casts = [
        'date_echeance' => 'date',
    ];

    public function emprunt()
    {
        return $this->belongsTo(Emprunt::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
}
