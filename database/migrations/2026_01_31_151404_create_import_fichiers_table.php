<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fichiers_import', function (Blueprint $table) {
            $table->id();
            $table->string('nom_fichier')->comment('Nom du fichier importé');
            $table->string('chemin_fichier')->comment('Chemin du fichier sur le serveur');
            $table->string('type_import')->comment('Type d\'import (ex: clients, contrats, cotisations)');
            $table->enum('statut_import', ['en_attente', 'en_cours', 'termine', 'erreur', 'annule'])->default('en_attente')->comment('Statut de l\'import');
            $table->integer('nombre_lignes')->default(0)->comment('Nombre total de lignes dans le fichier');
            $table->integer('lignes_traitees')->default(0)->comment('Nombre de lignes traitées avec succès');
            $table->integer('lignes_erreur')->default(0)->comment('Nombre de lignes en erreur');
            $table->json('resultat_import')->nullable()->comment('Résultats détaillés de l\'import');
            $table->json('erreurs_detaillees')->nullable()->comment('Erreurs détaillées par ligne');
            $table->foreignId('importe_par')->nullable()->constrained('users')->comment('Utilisateur ayant lancé l\'import');
            $table->dateTime('date_debut_import')->nullable()->comment('Date de début de l\'import');
            $table->dateTime('date_fin_import')->nullable()->comment('Date de fin de l\'import');
            $table->text('notes')->nullable()->comment('Notes supplémentaires');
            $table->timestamps();

            // Index
            $table->index('type_import');
            $table->index('statut_import');
            $table->index('importe_par');
            $table->index('date_debut_import');
            $table->index('date_fin_import');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichiers_import');
    }
};
