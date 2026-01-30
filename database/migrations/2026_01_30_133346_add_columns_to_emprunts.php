<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emprunts', function (Blueprint $table) {
            // Colonnes pour le processus bancaire
            $table->boolean('est_actif')->default(true)->after('status');
            $table->integer('duree_jours')->nullable()->after('duree_mois');

            // Colonnes pour les notifications et suivi
            $table->boolean('notifie_approuve')->default(false);
            $table->boolean('notifie_fonds_disponibles')->default(false);
            $table->timestamp('date_notification_approuve')->nullable();
            $table->timestamp('date_notification_fonds')->nullable();

            // Colonnes pour le paiement automatique
            $table->boolean('paiement_automatique')->default(false);
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->date('prochaine_date_prelevement')->nullable();
            $table->date('dernier_prelevement')->nullable();

            // Vérifications bancaires
            $table->foreignId('conseiller_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('date_etude')->nullable();
            $table->enum('score_credit', ['A', 'B', 'C', 'D', 'E'])->nullable();
            $table->decimal('capacite_remboursement', 15, 2)->nullable();
            $table->decimal('endettement_actuel', 5, 2)->nullable();
            $table->boolean('verification_identite')->default(false);
            $table->boolean('verification_revenus')->default(false);
            $table->boolean('verification_emploi')->default(false);

            // Index pour optimiser les requêtes
            $table->index(['user_id', 'est_actif', 'status']);
            $table->index(['status', 'date_debut']);
        });

        // Table pour les décisions (pour Filament)
        if (!Schema::hasTable('decisions_emprunt')) {
            Schema::create('decisions_emprunt', function (Blueprint $table) {
                $table->id();
                $table->foreignId('emprunt_id')->constrained()->onDelete('cascade');
                $table->foreignId('conseiller_id')->nullable()->constrained('users')->onDelete('set null');

                $table->enum('decision', ['approuve', 'refuse', 'en_attente', 'conditionnel']);
                $table->decimal('taux_propose', 5, 2)->nullable();
                $table->decimal('montant_propose', 15, 2)->nullable();
                $table->integer('duree_proposee')->nullable();
                $table->text('conditions')->nullable();
                $table->text('raison_refus')->nullable();
                $table->text('commentaires')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions_emprunt');

        Schema::table('emprunts', function (Blueprint $table) {
            $table->dropColumn([
                'est_actif',
                'taux_interet_mensuel',
                'duree_jours',
                'notifie_approuve',
                'notifie_fonds_disponibles',
                'date_notification_approuve',
                'date_notification_fonds',
                'paiement_automatique',
                'iban',
                'bic',
                'prochaine_date_prelevement',
                'dernier_prelevement',
                'conseiller_id',
                'date_etude',
                'score_credit',
                'capacite_remboursement',
                'endettement_actuel',
                'verification_identite',
                'verification_revenus',
                'verification_emploi'
            ]);

            $table->dropIndex(['user_id', 'est_actif', 'status']);
            $table->dropIndex(['status', 'date_debut']);
        });
    }
};
