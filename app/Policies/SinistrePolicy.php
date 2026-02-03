<?php

namespace App\Policies;

use App\Models\Sinistre;
use App\Models\User;

class SinistrePolicy
{
    // public function viewAny(User $user): bool
    // {
    //     return $user->hasAnyPermission(['view_sinistres', 'manage_sinistres']);
    // }

    // public function view(User $user, Sinistre $sinistre): bool
    // {
    //     // Admins et actuaires peuvent tout voir
    //     if ($user->hasRole('admin') || $user->hasRole('actuaire')) {
    //         return true;
    //     }

    //     // Experts voient leurs sinistres assignés
    //     if ($user->hasRole('agent') && $user->agent->id === $sinistre->expert_id) {
    //         return true;
    //     }

    //     // Agents voient les sinistres de leurs clients
    //     if ($user->hasRole('agent')) {
    //         return $sinistre->contrat->agent_id === $user->agent->id;
    //     }

    //     // Clients voient leurs propres sinistres
    //     if ($user->hasRole('client')) {
    //         return $sinistre->contrat->souscripteur->utilisateur_id === $user->id;
    //     }

    //     return false;
    // }

    // public function create(User $user): bool
    // {
    //     return $user->hasAnyPermission(['create_sinistres', 'manage_sinistres']);
    // }

    // public function update(User $user, Sinistre $sinistre): bool
    // {
    //     // Admins peuvent tout modifier
    //     if ($user->hasRole('admin')) {
    //         return true;
    //     }

    //     // Experts peuvent modifier leurs sinistres assignés
    //     if ($user->hasRole('agent') && $user->agent->id === $sinistre->expert_id) {
    //         return true;
    //     }

    //     return false;
    // }

    // public function delete(User $user, Sinistre $sinistre): bool
    // {
    //     return $user->hasRole('admin') && $user->hasPermissionTo('delete_sinistres');
    // }

    // public function assignExpert(User $user, Sinistre $sinistre): bool
    // {
    //     return $user->hasAnyPermission(['assign_expert', 'manage_sinistres']);
    // }

    // public function acceptClaim(User $user, Sinistre $sinistre): bool
    // {
    //     return $user->hasAnyPermission(['accept_claims', 'manage_sinistres']);
    // }

    // public function refuseClaim(User $user, Sinistre $sinistre): bool
    // {
    //     return $user->hasAnyPermission(['refuse_claims', 'manage_sinistres']);
    // }

    // public function payClaim(User $user, Sinistre $sinistre): bool
    // {
    //     return $user->hasAnyPermission(['pay_claims', 'manage_sinistres']);
    // }
}
