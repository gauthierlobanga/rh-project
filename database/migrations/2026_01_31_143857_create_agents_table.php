<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->unique()->comment('Lien vers l\'utilisateur');
            $table->string('matricule_agent')->unique()->comment('Matricule unique de l\'agent');
            $table->string('numero_agrement')->nullable()->comment('Numéro d\'agrément');
            $table->date('date_expiration_agrement')->nullable()->comment('Date d\'expiration de l\'agrément');
            $table->enum('statut_agent', ['actif', 'inactif', 'suspendu'])->default('actif')->comment('Statut de l\'agent');
            $table->decimal('taux_commission', 5, 2)->default(0)->comment('Taux de commission par défaut (%)');
            $table->json('coordonnees_professionnelles')->nullable()->comment('Coordonnées professionnelles');
            $table->json('specialisations')->nullable()->comment('Spécialisations de l\'agent');
            $table->decimal('objectif_annuel', 15, 2)->nullable()->comment('Objectif de vente annuel');
            $table->decimal('performance_annuelle', 5, 2)->nullable()->comment('Performance annuelle (%)');
            $table->string('agence_affectation')->nullable()->comment('Agence d\'affectation');
            $table->timestamps();

            $table->index('matricule_agent');
            $table->index('statut_agent');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
