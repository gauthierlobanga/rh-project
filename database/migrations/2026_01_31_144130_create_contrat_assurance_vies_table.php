<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrat_assurance_vies', function (Blueprint $table) {
            $table->id();
            $table->string('numero_contrat')->unique()->comment('Numéro unique du contrat');
            $table->foreignId('souscripteur_id')->constrained('clients')->comment('Client souscripteur');
            $table->foreignId('produit_id')->constrained('produit_assurances')->comment('Produit souscrit');
            $table->foreignId('agent_id')->nullable()->constrained('agents')->comment('Agent commercial');
            $table->decimal('capital_assure', 15, 2)->comment('Capital assuré en euros');
            $table->decimal('prime_annuelle', 15, 2)->comment('Prime annuelle en euros');
            $table->enum('frequence_paiement', ['mensuelle', 'trimestrielle', 'semestrielle', 'annuelle'])->comment('Fréquence de paiement');
            $table->decimal('montant_periodicite', 15, 2)->comment('Montant par périodicité');
            $table->date('date_effet')->comment('Date d\'effet du contrat');
            $table->date('date_echeance')->comment('Date d\'échéance du contrat');
            $table->integer('duree_contrat')->comment('Durée du contrat en années');
            $table->enum('statut_contrat', ['en_attente', 'actif', 'suspendu', 'resilie', 'echeance_atteinte', 'deces'])->default('en_attente')->comment('Statut du contrat');
            $table->enum('mode_paiement', ['prelevement', 'virement', 'cheque', 'carte'])->comment('Mode de paiement');
            $table->json('coordonnees_paiement')->nullable()->comment('Coordonnées de paiement');
            $table->json('options_souscrites')->nullable()->comment('Options souscrites');
            $table->text('conditions_particulieres')->nullable()->comment('Conditions particulières');
            $table->decimal('frais_gestion', 5, 2)->nullable()->comment('Frais de gestion (%)');
            $table->decimal('frais_entree', 5, 2)->nullable()->comment('Frais d\'entrée (%)');
            $table->decimal('frais_sortie', 5, 2)->nullable()->comment('Frais de sortie (%)');
            $table->decimal('participation_benefices', 5, 2)->nullable()->comment('Participation aux bénéfices (%)');
            $table->date('date_signature')->nullable()->comment('Date de signature');
            $table->date('date_validation')->nullable()->comment('Date de validation');
            $table->date('date_resiliation')->nullable()->comment('Date de résiliation');
            $table->string('motif_resiliation')->nullable()->comment('Motif de résiliation');
            $table->decimal('valeur_rachat', 15, 2)->nullable()->comment('Valeur de rachat actuelle');
            $table->decimal('valeur_epargne', 15, 2)->nullable()->comment('Valeur de l\'épargne');
            $table->json('parametres_calcul')->nullable()->comment('Paramètres de calcul');
            $table->string('numero_police')->nullable()->unique()->comment('Numéro de police');
            $table->timestamps();
            $table->softDeletes()->comment('Suppression douce');

            // Index pour les recherches fréquentes
            $table->index('numero_contrat');
            $table->index('souscripteur_id');
            $table->index('produit_id');
            $table->index('agent_id');
            $table->index('statut_contrat');
            $table->index('date_effet');
            $table->index('date_echeance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrat_assurance_vies');
    }
};
