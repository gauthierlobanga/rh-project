<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achats_rachats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained('contrats_assurance_vie')->comment('Contrat concerné');
            $table->enum('type_operation', ['achat', 'rachat_partiel', 'rachat_total'])->comment('Type d\'opération');
            $table->date('date_operation')->comment('Date de l\'opération');
            $table->decimal('montant_operation', 15, 2)->comment('Montant de l\'opération');
            $table->decimal('frais_operation', 15, 2)->default(0)->comment('Frais liés à l\'opération');
            $table->decimal('montant_nette', 15, 2)->comment('Montant net après frais');
            $table->decimal('valeur_unitaire', 15, 4)->nullable()->comment('Valeur unitaire au moment de l\'opération');
            $table->decimal('nombre_parts', 15, 4)->nullable()->comment('Nombre de parts achetées/rachatées');
            $table->json('details_operation')->nullable()->comment('Détails de l\'opération');
            $table->enum('statut_operation', ['demande', 'validee', 'executée', 'refusee', 'annulee'])->default('demande')->comment('Statut de l\'opération');
            $table->foreignId('valide_par')->nullable()->constrained('users')->comment('Validée par');
            $table->date('date_validation')->nullable()->comment('Date de validation');
            $table->text('motif_refus')->nullable()->comment('Motif de refus');
            $table->date('date_execution')->nullable()->comment('Date d\'exécution');
            $table->string('reference_operation')->nullable()->unique()->comment('Référence de l\'opération');
            $table->json('parametres_fiscaux')->nullable()->comment('Paramètres fiscaux');
            $table->text('notes')->nullable()->comment('Notes');
            $table->timestamps();

            // Index
            $table->index('contrat_id');
            $table->index('type_operation');
            $table->index('statut_operation');
            $table->index('date_operation');
            $table->index('reference_operation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achats_rachats');
    }
};
