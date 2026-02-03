<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained('contrat_assurance_vies')->comment('Contrat concerné');
            $table->date('date_echeance')->comment('Date d\'échéance');
            $table->date('date_paiement')->nullable()->comment('Date effective de paiement');
            $table->decimal('montant_due', 15, 2)->comment('Montant dû');
            $table->decimal('montant_paye', 15, 2)->default(0)->comment('Montant effectivement payé');
            $table->enum('statut_paiement', [
                'en_attente',
                'paye',
                'partiellement_paye',
                'en_retard',
                'annule',
                'impaye',
            ])->default('en_attente')->comment('Statut du paiement');
            $table->string('numero_facture')->nullable()->unique()->comment('Numéro de facture');
            $table->json('details_paiement')->nullable()->comment('Détails du paiement');
            $table->decimal('penalite_retard', 10, 2)->default(0)->comment('Pénalité pour retard');
            $table->decimal('interets_moratoires', 10, 2)->default(0)->comment('Intérêts moratoires');
            $table->enum('mode_paiement', ['prelevement', 'virement', 'cheque', 'carte', 'especes'])->nullable()->comment('Mode de paiement utilisé');
            $table->string('reference_paiement')->nullable()->comment('Référence du paiement');
            $table->date('date_encaissement')->nullable()->comment('Date d\'encaissement');
            $table->text('notes')->nullable()->comment('Notes internes');
            $table->boolean('est_rappele')->default(false)->comment('Rappel envoyé ou non');
            $table->date('date_rappel')->nullable()->comment('Date du dernier rappel');
            $table->integer('nombre_relances')->default(0)->comment('Nombre de relances effectuées');
            $table->timestamps();

            // Index pour les recherches et rapports
            $table->index('contrat_id');
            $table->index('date_echeance');
            $table->index('date_paiement');
            $table->index('statut_paiement');
            $table->index('numero_facture');
            $table->index('created_at');

            // Index composé pour les requêtes fréquentes
            $table->index(['contrat_id', 'date_echeance']);
            $table->index(['statut_paiement', 'date_echeance']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotisations');
    }
};
