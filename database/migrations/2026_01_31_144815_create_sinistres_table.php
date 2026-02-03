<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinistres', function (Blueprint $table) {
            $table->id();
            $table->string('numero_sinistre')->unique()->comment('Numéro unique du sinistre');
            $table->foreignId('contrat_id')->constrained('contrat_assurance_vies')->comment('Contrat concerné');
            $table->enum('type_sinistre', [
                'deces',
                'invalidite',
                'maladie_grave',
                'rachat',
                'avance',
                'garantie_dependance'
            ])->comment('Type de sinistre');
            $table->date('date_survenance')->comment('Date de survenance');
            $table->date('date_declaration')->comment('Date de déclaration');
            $table->date('date_notification')->comment('Date de notification à l\'assureur');
            $table->text('description_sinistre')->comment('Description détaillée');
            $table->json('documents_requis')->nullable()->comment('Liste des documents requis');
            $table->json('documents_recus')->nullable()->comment('Documents reçus');
            $table->decimal('montant_reclame', 15, 2)->comment('Montant réclamé');
            $table->decimal('montant_accordee', 15, 2)->nullable()->comment('Montant accordé');
            $table->decimal('montant_indemnise', 15, 2)->nullable()->comment('Montant indemnisé');
            $table->enum('statut_sinistre', [
                'declare',
                'en_cours_examen',
                'documents_manquants',
                'expertise_en_cours',
                'accepte',
                'refuse',
                'indemnise',
                'cloture'
            ])->default('declare')->comment('Statut du sinistre');
            $table->foreignId('expert_id')->nullable()->constrained('agents')->comment('Expert assigné');
            $table->text('notes_expert')->nullable()->comment('Notes de l\'expert');
            $table->text('motif_refus')->nullable()->comment('Motif de refus le cas échéant');
            $table->date('date_traitement')->nullable()->comment('Date de traitement');
            $table->date('date_indemnisation')->nullable()->comment('Date d\'indemnisation');
            $table->json('beneficiaires_indemnisation')->nullable()->comment('Bénéficiaires de l\'indemnisation');
            $table->string('numero_virement')->nullable()->comment('Numéro de virement');
            $table->text('commentaires_internes')->nullable()->comment('Commentaires internes');
            $table->boolean('est_fraude_suspectee')->default(false)->comment('Fraude suspectée');
            $table->text('notes_fraude')->nullable()->comment('Notes sur la suspicion de fraude');
            $table->timestamps();

            // Index
            $table->index('numero_sinistre');
            $table->index('contrat_id');
            $table->index('type_sinistre');
            $table->index('statut_sinistre');
            $table->index('date_survenance');
            $table->index('date_declaration');
            $table->index('expert_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinistres');
    }
};
