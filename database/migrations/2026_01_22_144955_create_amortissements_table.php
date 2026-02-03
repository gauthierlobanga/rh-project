<?php

// use App\Models\User;
// use Illuminate\Support\Facades\Schema;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Database\Migrations\Migration;

// return new class extends Migration
// {
//     public function up(): void
//     {

//         Schema::create('emprunts', function (Blueprint $table) {
//             $table->id();
//             $table->foreignId('user_id')->constrained()->onDelete('cascade');
//             // Données fournies par le client
//             $table->decimal('montant_emprunt', 15, 2);
//             $table->date('date_fin_remboursement');
//             // Paramètres additionnels
//             $table->decimal('taux_interet_annuel', 5, 2); // ex: 5.25%
//             $table->enum('type_amortissement', ['constant', 'decroissant'])->default('constant');
//             $table->enum('frequence_paiement', ['mensuel', 'trimestriel', 'annuel'])->default('mensuel');
//             $table->date('date_debut');
//             $table->integer('duree_mois')->nullable(); // Calculée automatiquement

//             // Calculs automatiques
//             $table->decimal('montant_mensualite', 15, 2)->nullable();
//             $table->decimal('total_interets', 15, 2)->nullable();
//             $table->decimal('total_a_rembourser', 15, 2)->nullable();
//             $table->decimal('taeg', 5, 2)->nullable();

//             // Statut
//             $table->enum('status', ['en_attente', 'approuve', 'en_cours', 'termine', 'defaut'])->default('en_attente');

//             $table->text('notes')->nullable();
//             $table->timestamps();
//         });

//         Schema::create('echeances', function (Blueprint $table) {
//             $table->id();
//             $table->foreignId('emprunt_id')->constrained()->onDelete('cascade');

//             // Identification de l'échéance
//             $table->integer('numero_echeance');
//             $table->date('date_echeance');
//             $table->boolean('est_payee')->default(false);
//             $table->date('date_paiement')->nullable();

//             // Calculs détaillés
//             $table->decimal('capital_initial', 15, 2); // Capital restant avant paiement
//             $table->decimal('montant_echeance', 15, 2); // Mensualité
//             $table->decimal('part_interets', 15, 2); // Intérêt de la période
//             $table->decimal('part_capital', 15, 2); // Capital remboursé
//             $table->decimal('capital_restant', 15, 2); // Nouveau solde

//             // Cumuls
//             $table->decimal('interets_cumules', 15, 2); // Total intérêts payés jusqu'à cette échéance
//             $table->decimal('capital_cumule', 15, 2); // Total capital remboursé

//             $table->timestamps();

//             // Index pour optimiser les recherches
//             $table->index(['emprunt_id', 'date_echeance']);
//             $table->index(['est_payee', 'date_echeance']);
//         });

//         Schema::create('paiements', function (Blueprint $table) {
//             $table->id();
//             $table->foreignId('echeance_id')->constrained()->onDelete('cascade');
//             $table->foreignId('emprunt_id')->constrained()->onDelete('cascade');

//             $table->decimal('montant_paye', 15, 2);
//             $table->date('date_paiement');
//             $table->enum('mode_paiement', ['virement', 'prelevement', 'cheque', 'especes']);
//             $table->string('reference_paiement')->nullable();

//             // En cas de paiement partiel ou anticipé
//             $table->boolean('est_partiel')->default(false);
//             $table->decimal('montant_restant', 15, 2)->nullable();

//             $table->text('notes')->nullable();
//             $table->timestamps();
//         });
//     }

//     public function down(): void
//     {
//         Schema::dropIfExists('paiements');
//         Schema::dropIfExists('echeances');
//         Schema::dropIfExists('emprunts');
//     }
// };

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emprunts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Données fournies par le client
            $table->decimal('montant_emprunt', 15, 2);
            $table->date('date_fin_remboursement');

            // Paramètres additionnels
            $table->decimal('taux_interet_annuel', 5, 2);
            $table->enum('type_amortissement', ['constant', 'decroissant'])->default('constant');
            $table->enum('frequence_paiement', ['mensuel', 'trimestriel', 'annuel'])->default('mensuel');
            $table->date('date_debut');
            $table->integer('duree_mois')->nullable();

            // Frais
            $table->decimal('frais_dossier', 15, 2)->default(0);
            $table->decimal('frais_assurance', 15, 2)->default(0);
            $table->decimal('frais_notaire', 15, 2)->default(0);
            $table->decimal('frais_autres', 15, 2)->default(0);

            // Calculs automatiques
            $table->decimal('montant_mensualite', 15, 2)->nullable();
            $table->decimal('total_interets', 15, 2)->nullable();
            $table->decimal('total_a_rembourser', 15, 2)->nullable();
            $table->decimal('total_frais', 15, 2)->nullable();
            $table->decimal('montant_total_du', 15, 2)->nullable();
            $table->decimal('taeg', 5, 2)->nullable();

            // Dates importantes
            $table->date('date_approbation')->nullable();
            $table->date('date_signature')->nullable();
            $table->date('date_deblocage')->nullable();

            // Statut
            $table->enum('status', ['en_attente', 'approuve', 'refuse', 'en_cours', 'termine', 'defaut'])->default('en_attente');

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('echeances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emprunt_id')->constrained()->onDelete('cascade');

            // Identification de l'échéance
            $table->integer('numero_echeance');
            $table->date('date_echeance');
            $table->boolean('est_payee')->default(false);
            $table->date('date_paiement')->nullable();

            // Calculs détaillés
            $table->decimal('capital_initial', 15, 2);
            $table->decimal('montant_echeance', 15, 2);
            $table->decimal('part_interets', 15, 2);
            $table->decimal('part_capital', 15, 2);
            $table->decimal('part_assurance', 15, 2)->default(0);
            $table->decimal('capital_restant', 15, 2);

            // Cumuls
            $table->decimal('interets_cumules', 15, 2);
            $table->decimal('capital_cumule', 15, 2);
            $table->decimal('assurance_cumulee', 15, 2)->default(0);

            $table->timestamps();

            $table->index(['emprunt_id', 'date_echeance']);
            $table->index(['est_payee', 'date_echeance']);
        });

        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('echeance_id')->constrained()->onDelete('cascade');
            $table->foreignId('emprunt_id')->constrained()->onDelete('cascade');

            $table->decimal('montant_paye', 15, 2);
            $table->date('date_paiement');
            $table->enum('mode_paiement', ['virement', 'prelevement', 'cheque', 'especes', 'carte']);
            $table->string('reference_paiement')->nullable();

            $table->boolean('est_partiel')->default(false);
            $table->decimal('montant_restant', 15, 2)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('frais_emprunt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emprunt_id')->constrained()->onDelete('cascade');
            $table->string('type'); // dossier, assurance, notaire, autres
            $table->string('description');
            $table->decimal('montant', 15, 2);
            $table->date('date_facturation')->nullable();
            $table->boolean('est_paye')->default(false);
            $table->date('date_paiement')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frais_emprunt');
        Schema::dropIfExists('paiements');
        Schema::dropIfExists('echeances');
        Schema::dropIfExists('emprunts');
    }
};
