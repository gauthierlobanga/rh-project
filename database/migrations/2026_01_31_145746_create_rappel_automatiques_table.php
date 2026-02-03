<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rappels_automatiques', function (Blueprint $table) {
            $table->id();
            $table->string('type_rappel')->comment('Type de rappel');
            $table->string('sujet_rappel')->comment('Sujet du rappel');
            $table->text('description')->comment('Description du rappel');
            $table->unsignedBigInteger('destinataire_id')->nullable()->comment('ID du destinataire');
            $table->string('destinataire_type')->nullable()->comment('Type du destinataire (polymorphique)');
            $table->string('mode_envoi')->comment('Mode d\'envoi principal');
            $table->enum('statut_envoi', ['programme', 'envoye', 'echoue', 'receptionne'])->default('programme')->comment('Statut d\'envoi');
            $table->dateTime('date_programmation')->comment('Date de programmation');
            $table->dateTime('date_envoi')->nullable()->comment('Date d\'envoi');
            $table->dateTime('date_reception')->nullable()->comment('Date de réception');
            $table->string('frequence_rappel')->nullable()->comment('Fréquence du rappel');
            $table->integer('nombre_tentatives')->default(0)->comment('Nombre de tentatives');
            $table->integer('max_tentatives')->default(3)->comment('Nombre maximum de tentatives');
            $table->integer('delai_entre_tentatives')->default(24)->comment('Délai entre tentatives (en heures)');
            $table->json('canaux_envoi')->nullable()->comment('Canaux d\'envoi multiples');
            $table->json('modeles_utilises')->nullable()->comment('Modèles utilisés pour l\'envoi');
            $table->json('parametres_envoi')->nullable()->comment('Paramètres d\'envoi');
            $table->json('reponse_destinataire')->nullable()->comment('Réponse du destinataire');
            $table->json('erreurs_envoi')->nullable()->comment('Erreurs d\'envoi');
            $table->boolean('est_urgent')->default(false)->comment('Rappel urgent');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'critique'])->default('normale')->comment('Priorité du rappel');
            $table->json('tags')->nullable()->comment('Tags pour catégorisation');
            $table->unsignedBigInteger('campagne_id')->nullable()->comment('ID de la campagne associée');
            $table->string('lien_element')->nullable()->comment('Lien vers un élément spécifique');
            $table->timestamps();

            // Index pour les performances
            $table->index(['destinataire_id', 'destinataire_type']);
            $table->index('type_rappel');
            $table->index('statut_envoi');
            $table->index('date_programmation');
            $table->index('date_envoi');
            $table->index('est_urgent');
            $table->index('priorite');
            $table->index('campagne_id');
            $table->index('lien_element');
            $table->index('mode_envoi');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rappels_automatiques');
    }
};
