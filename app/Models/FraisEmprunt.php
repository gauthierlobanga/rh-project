<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FraisEmprunt extends Model
{
    use HasFactory;

    protected $fillable = [
        'emprunt_id',
        'type',
        'description',
        'montant',
        'date_facturation',
        'est_paye',
        'date_paiement',
    ];

    protected $casts = [
        'date_facturation' => 'date',
        'date_paiement' => 'date',
    ];

    public function emprunt()
    {
        return $this->belongsTo(Emprunt::class);
    }
}
