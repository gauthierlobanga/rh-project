<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('reference_client')->unique()->comment('Référence unique du client');
            $table->enum('type_client', ['particulier', 'entreprise'])->default('particulier')->comment('Type de client');
            $table->string('civilite')->nullable()->comment('Civilité (M., Mme, Mlle)');
            $table->string('nom')->nullable()->comment('Nom de famille');
            $table->string('prenom')->nullable()->comment('Prénom');
            $table->date('date_naissance')->comment('Date de naissance');
            $table->string('lieu_naissance')->nullable()->comment('Lieu de naissance');
            $table->string('nationalite')->default('Française')->comment('Nationalité');
            $table->string('profession')->nullable()->comment('Profession');
            $table->string('numero_cni')->nullable()->unique()->comment('Numéro de carte nationale d\'identité');
            $table->date('date_expiration_cni')->nullable()->comment('Date d\'expiration de la CNI');
            $table->string('email')->unique()->comment('Adresse email');
            $table->string('telephone_fixe')->nullable()->comment('Téléphone fixe');
            $table->string('telephone_mobile')->comment('Téléphone mobile');
            $table->json('adresse')->comment('Adresse complète au format JSON');
            $table->json('coordonnees_bancaires')->nullable()->comment('Coordonnées bancaires (IBAN, BIC, banque)');
            $table->enum('situation_familiale', ['celibataire', 'marie', 'divorce', 'veuf'])->nullable()->comment('Situation familiale');
            $table->integer('nombre_enfants')->default(0)->comment('Nombre d\'enfants à charge');
            $table->decimal('revenu_annuel', 15, 2)->nullable()->comment('Revenu annuel en euros');
            $table->json('profil_risque')->nullable()->comment('Profil de risque du client');
            $table->boolean('kyc_verifie')->default(false)->comment('KYC vérifié oui/non');
            $table->date('date_verification_kyc')->nullable()->comment('Date de vérification KYC');
            $table->text('notes')->nullable()->comment('Notes internes');
            $table->foreignId('agent_id')->nullable()->constrained('agents')->comment('Agent assigné');
            $table->string('source_acquisition')->nullable()->comment('Source d\'acquisition du client');
            $table->timestamps();
            $table->softDeletes()->comment('Suppression douce');

            // Index pour les recherches fréquentes
            $table->index(['nom', 'prenom']);
            $table->index('email');
            $table->index('telephone_mobile');
            $table->index('agent_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
