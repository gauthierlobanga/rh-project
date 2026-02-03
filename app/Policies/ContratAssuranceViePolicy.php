<?php

namespace App\Policies;

use App\Models\ContratAssuranceVie;
use App\Models\User;

class ContratAssuranceViePolicy
{
    // public function viewAny(User $user): bool
    // {
    //     return $user->hasAnyPermission(['view_contrats', 'manage_contrats']);
    // }

    // public function view(User $user, ContratAssuranceVie $contrat): bool
    // {
    //     // Admins et actuaires peuvent tout voir
    //     if ($user->hasRole('admin') || $user->hasRole('actuaire')) {
    //         return true;
    //     }

    //     // Agents voient leurs contrats
    //     if ($user->hasRole('agent')) {
    //         return $contrat->agent_id === $user->agent->id;
    //     }

    //     // Clients voient leurs propres contrats
    //     if ($user->hasRole('client')) {
    //         return $contrat->souscripteur->utilisateur_id === $user->id;
    //     }

    //     return false;
    // }

    // public function create(User $user): bool
    // {
    //     return $user->hasAnyPermission(['create_contrats', 'manage_contrats']);
    // }

    // public function update(User $user, ContratAssuranceVie $contrat): bool
    // {
    //     // Admins et actuaires peuvent modifier tous les contrats
    //     if ($user->hasRole('admin') || $user->hasRole('actuaire')) {
    //         return true;
    //     }

    //     // Agents ne peuvent modifier que leurs contrats non actifs
    //     if ($user->hasRole('agent')) {
    //         return $contrat->agent_id === $user->agent->id &&
    //             $contrat->statut_contrat !== 'actif';
    //     }

    //     return false;
    // }

    // public function delete(User $user, ContratAssuranceVie $contrat): bool
    // {
    //     // Seuls les admins peuvent supprimer des contrats
    //     return $user->hasRole('admin') &&
    //         $user->hasPermissionTo('delete_contrats') &&
    //         $contrat->statut_contrat !== 'actif';
    // }

    // public function validateContrat(User $user, ContratAssuranceVie $contrat): bool
    // {
    //     return $user->hasAnyPermission(['validate_contrats', 'manage_contrats']);
    // }

    // public function resiliateContrat(User $user, ContratAssuranceVie $contrat): bool
    // {
    //     // Admins peuvent toujours résilier
    //     if ($user->hasRole('admin')) {
    //         return true;
    //     }

    //     // Agents ne peuvent résilier que leurs contrats
    //     if ($user->hasRole('agent')) {
    //         return $contrat->agent_id === $user->agent->id &&
    //             $user->hasPermissionTo('resiliate_contrats');
    //     }

    //     return false;
    // }

    // public function addBeneficiary(User $user, ContratAssuranceVie $contrat): bool
    // {
    //     // Admins et actuaires peuvent toujours ajouter
    //     if ($user->hasRole('admin') || $user->hasRole('actuaire')) {
    //         return true;
    //     }

    //     // Agents pour leurs contrats
    //     if ($user->hasRole('agent')) {
    //         return $contrat->agent_id === $user->agent->id;
    //     }

    //     // Clients pour leurs propres contrats
    //     if ($user->hasRole('client')) {
    //         return $contrat->souscripteur->utilisateur_id === $user->id;
    //     }

    //     return false;
    // }

    // public function viewActuarial(User $user, ContratAssuranceVie $contrat): bool
    // {
    //     // Seuls les admins et actuaires peuvent voir les données actuarielles
    //     return $user->hasRole('admin') || $user->hasRole('actuaire');
    // }
}
