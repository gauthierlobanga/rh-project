<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_assurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destinataire_id')->constrained('users')->comment('Destinataire de la notification');
            $table->enum('type_notification', [
                'rappel_paiement',
                'echeance_proche',
                'sinistre_declare',
                'sinistre_traite',
                'contrat_active',
                'contrat_resilie',
                'document_disponible',
                'commission_calculee',
                'commission_payee',
                'alerte_securite',
                'information_generale'
            ])->comment('Type de notification');
            $table->string('titre')->comment('Titre de la notification');
            $table->text('contenu')->comment('Contenu de la notification');
            $table->json('donnees_liees')->nullable()->comment('Données liées à la notification');
            $table->boolean('est_lue')->default(false)->comment('Notification lue ou non');
            $table->timestamp('date_lecture')->nullable()->comment('Date de lecture');
            $table->enum('canal_envoi', ['email', 'sms', 'application', 'tous'])->default('application')->comment('Canal d\'envoi');
            $table->boolean('est_envoyee')->default(false)->comment('Notification envoyée');
            $table->timestamp('date_envoi')->nullable()->comment('Date d\'envoi');
            $table->text('erreur_envoi')->nullable()->comment('Erreur d\'envoi le cas échéant');
            $table->integer('tentatives_envoi')->default(0)->comment('Nombre de tentatives d\'envoi');
            $table->boolean('est_urgente')->default(false)->comment('Notification urgente');
            $table->date('date_expiration')->nullable()->comment('Date d\'expiration de la notification');
            $table->timestamps();

            // Index
            $table->index('destinataire_id');
            $table->index('type_notification');
            $table->index('est_lue');
            $table->index('est_envoyee');
            $table->index('est_urgente');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_assurances');
    }
};
