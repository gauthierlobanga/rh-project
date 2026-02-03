<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->comment('Lien vers l\'utilisateur');
            $table->foreignId('contrat_id')->constrained('contrat_assurance_vies')->comment('Contrat concerné');
            $table->string('nom')->comment('Nom du bénéficiaire');
            $table->string('prenom')->comment('Prénom du bénéficiaire');
            $table->date('date_naissance')->nullable()->comment('Date de naissance');
            $table->string('lien_parente')->comment('Lien de parenté avec le souscripteur');
            $table->decimal('pourcentage_attribution', 5, 2)->comment('Pourcentage d\'attribution');
            $table->boolean('est_beneficiaire_primaire')->default(false)->comment('Bénéficiaire primaire ou non');
            $table->json('coordonnees_contact')->nullable()->comment('Coordonnées de contact');
            $table->string('numero_cni')->nullable()->comment('Numéro de CNI');
            $table->date('date_effet_attribution')->nullable()->comment('Date d\'effet de l\'attribution');
            $table->date('date_fin_attribution')->nullable()->comment('Date de fin d\'attribution');
            $table->enum('statut_beneficiaire', ['actif', 'inactif', 'decede'])->default('actif')->comment('Statut du bénéficiaire');
            $table->text('conditions_particulieres')->nullable()->comment('Conditions particulières');
            $table->timestamps();

            // Index
            $table->index('contrat_id');
            $table->index(['nom', 'prenom']);
            $table->index('lien_parente');
            $table->index('est_beneficiaire_primaire');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiaires');
    }
};
