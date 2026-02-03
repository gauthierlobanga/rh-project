<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historique_contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained('contrat_assurance_vies')->comment('Contrat concerné');
            $table->enum('type_evenement', [
                'creation',
                'modification',
                'modification_beneficiaire',
                'changement_capital',
                'modification_prime',
                'changement_mode_paiement',
                'mise_a_jour_coordonnees',
                'resiliation',
                'rachat',
                'rachat_total',
                'rachat_partiel',
                'arbitrage',
                'consultation',
                'changement_statut',
                'paiement',
                'sinistre',
                'avance',
                'renouvellement',
                'notification',
                'autre',
            ])->comment('Type d\'événement');
            $table->text('description')->comment('Description de l\'événement');
            $table->json('donnees_avant')->nullable()->comment('Données avant l\'événement');
            $table->json('donnees_apres')->nullable()->comment('Données après l\'événement');
            $table->json('champs_modifies')->nullable()->comment('Champs modifiés');
            $table->unsignedBigInteger('utilisateur_id')->nullable()->comment('Utilisateur ayant effectué l\'action');
            $table->string('utilisateur_type')->nullable()->comment('Type d\'utilisateur (polymorphique)');
            $table->string('ip_adresse')->nullable()->comment('Adresse IP');
            $table->string('user_agent')->nullable()->comment('User agent du navigateur');
            $table->integer('niveau_importance')->default(1)->comment('Niveau d\'importance (1-10)');
            $table->json('tags')->nullable()->comment('Tags pour catégorisation');
            $table->string('reference_liee')->nullable()->comment('Référence à un autre objet');
            $table->string('type_reference_liee')->nullable()->comment('Type de référence liée');
            $table->date('date_effet')->nullable()->comment('Date d\'effet de la modification');
            $table->date('date_validation')->nullable()->comment('Date de validation');
            $table->foreignId('valide_par')->nullable()->constrained('users')->comment('Utilisateur ayant validé');
            $table->enum('statut_evenement', ['en_attente', 'valide', 'annule'])->default('en_attente')->comment('Statut de l\'événement');
            $table->text('notes')->nullable()->comment('Notes supplémentaires');
            $table->timestamps();

            // Index pour l'audit et les rapports
            $table->index('contrat_id');
            $table->index('type_evenement');
            $table->index('utilisateur_id');
            $table->index('statut_evenement');
            $table->index('niveau_importance');
            $table->index('date_effet');
            $table->index('created_at');
            $table->index(['contrat_id', 'type_evenement']);
            $table->index(['utilisateur_id', 'utilisateur_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_contrats');
    }
};
