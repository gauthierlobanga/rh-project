<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produit_assurances', function (Blueprint $table) {
            $table->id();
            $table->string('code_produit')->unique()->comment('Code unique du produit');
            $table->string('nom_produit')->comment('Nom commercial du produit');
            $table->string('description_courte')->nullable()->comment('Description courte');
            $table->text('description_longue')->nullable()->comment('Description détaillée');
            $table->enum('categorie', ['vie', 'retraite', 'epargne', 'education', 'investissement'])->comment('Catégorie du produit');
            $table->json('garanties_incluses')->comment('Liste des garanties incluses');
            $table->json('exclusions')->comment('Liste des exclusions');
            $table->json('options_disponibles')->nullable()->comment('Options disponibles');
            $table->integer('age_entree_minimum')->default(18)->comment('Âge minimum d\'entrée');
            $table->integer('age_entree_maximum')->default(65)->comment('Âge maximum d\'entrée');
            $table->integer('age_maturite_maximum')->default(85)->comment('Âge maximum à la maturité');
            $table->decimal('prime_minimale', 10, 2)->comment('Prime minimale acceptée');
            $table->decimal('prime_maximale', 15, 2)->nullable()->comment('Prime maximale acceptée');
            $table->decimal('capital_minimum', 15, 2)->comment('Capital minimum');
            $table->decimal('capital_maximum', 15, 2)->nullable()->comment('Capital maximum');
            $table->json('structure_commission')->nullable()->comment('Structure de commission');
            $table->json('parametres_actuariels')->nullable()->comment('Paramètres actuariels');
            $table->json('conditions_particulieres')->nullable()->comment('Conditions particulières');
            $table->boolean('est_actif')->default(true)->comment('Produit actif ou non');
            $table->date('date_activation')->nullable()->comment('Date d\'activation du produit');
            $table->date('date_desactivation')->nullable()->comment('Date de désactivation du produit');
            $table->string('document_contrat_type')->nullable()->comment('Type de document de contrat');
            $table->timestamps();

            $table->index('code_produit');
            $table->index('categorie');
            $table->index('est_actif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produit_assurances');
    }
};
