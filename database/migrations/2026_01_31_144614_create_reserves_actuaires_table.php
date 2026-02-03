<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserves_actuarielles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained('contrat_assurance_vies')->comment('Contrat concerné');
            $table->date('date_calcul')->comment('Date du calcul');
            $table->decimal('periode_calcul', 20, 2)->comment('Période de calcul');
            $table->string('methode_calcul')->comment('Méthode de calcul utilisée');
            $table->decimal('reserve_technique', 20, 2)->default(0)->comment('Réserve technique');
            $table->decimal('provision_risque', 20, 2)->default(0)->comment('Provision pour risques');
            $table->decimal('provision_previsionnelle', 20, 2)->comment('Provision prévisionnelle');
            $table->decimal('reserve_totale', 20, 2)->comment('Réserve totale');
            $table->decimal('taux_actualisation', 8, 4)->comment('Taux d\'actualisation');
            $table->decimal('taux_technique', 8, 4)->comment('Taux technique');
            $table->json('flux_futurs_projetes')->comment('Flux futurs projetés');
            $table->string('scenario_calcul')->comment('Scénario de calcul');
            $table->json('parametres_actuariels')->nullable()->comment('Paramètres actuariels');
            $table->json('resultats_detailes')->comment('Résultats détaillés');
            $table->enum('statut_calcul', ['validee', 'rejete', 'calculee', 'en_cours'])->default('en_cours')->comment('Statut du calcul');
            $table->foreignId('valide_par')->nullable()->constrained('users')->comment('Validé par');
            $table->date('date_validation')->nullable()->comment('Date de validation');
            $table->text('notes')->nullable()->comment('Observations sur le calcul');
            $table->decimal('ecart_avec_precedent', 20, 2)->nullable()->comment('Écart avec le calcul précédent');
            $table->decimal('evolution_percentage', 10, 2)->nullable()->comment('Évolution en pourcentage');
            $table->decimal('reserve_minimale_legale', 20, 2)->nullable()->comment('Réserve minimale légale');
            $table->decimal('marge_solvabilite', 20, 2)->nullable()->comment('Marge de solvabilité');
            $table->timestamps();

            // Index
            $table->index('contrat_id');
            $table->index('date_calcul');
            $table->index('reserve_totale');
            $table->index(['contrat_id', 'date_calcul']);
            $table->index('statut_calcul');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserves_actuarielles');
    }
};
