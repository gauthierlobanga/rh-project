<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->comment('Agent concerné');
            $table->foreignId('contrat_id')->constrained('contrats_assurance_vie')->comment('Contrat concerné');
            $table->foreignId('cotisation_id')->nullable()->constrained('cotisations')->comment('Cotisation concernée');
            $table->enum('type_commission', [
                'acquisition',
                'renouvellement',
                'perseverance',
                'performance',
                'bonus_speciale'
            ])->comment('Type de commission');
            $table->decimal('montant_prime', 15, 2)->comment('Montant de la prime');
            $table->decimal('taux_commission', 5, 2)->comment('Taux de commission (%)');
            $table->decimal('montant_commission', 15, 2)->comment('Montant de la commission');
            $table->date('date_calcul')->comment('Date de calcul');
            $table->date('date_paiement')->nullable()->comment('Date de paiement');
            $table->enum('statut_commission', [
                'calculee',
                'a_payer',
                'payee',
                'annulee',
                'reportee'
            ])->default('calculee')->comment('Statut de la commission');
            $table->string('numero_paiement')->nullable()->comment('Numéro de paiement');
            $table->json('details_calcul')->nullable()->comment('Détails du calcul');
            $table->integer('annee_comptable')->comment('Année comptable');
            $table->integer('mois_comptable')->comment('Mois comptable');
            $table->decimal('taux_tva', 5, 2)->nullable()->comment('Taux de TVA appliqué');
            $table->decimal('montant_tva', 15, 2)->nullable()->comment('Montant de TVA');
            $table->decimal('montant_net', 15, 2)->comment('Montant net à payer');
            $table->text('notes')->nullable()->comment('Notes');
            $table->timestamps();

            // Index
            $table->index('agent_id');
            $table->index('contrat_id');
            $table->index('cotisation_id');
            $table->index('type_commission');
            $table->index('statut_commission');
            $table->index('date_calcul');
            $table->index('date_paiement');
            $table->index(['annee_comptable', 'mois_comptable']);
            $table->index(['agent_id', 'statut_commission']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
