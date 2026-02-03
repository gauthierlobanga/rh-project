<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    // public function viewAny(User $user): bool
    // {
    //     return $user->hasAnyPermission(['view_clients', 'manage_clients']);
    // }

    // public function view(User $user, Client $client): bool
    // {
    //     // Les admins peuvent tout voir
    //     if ($user->hasRole('admin') || $user->hasRole('actuaire')) {
    //         return true;
    //     }

    //     // Les agents ne voient que leurs clients
    //     if ($user->hasRole('agent')) {
    //         return $client->agent_id === $user->agent->id;
    //     }

    //     // Les clients ne voient que leur propre profil
    //     if ($user->hasRole('client')) {
    //         return $client->utilisateur_id === $user->id;
    //     }

    //     return false;
    // }

    // public function create(User $user): bool
    // {
    //     return $user->hasAnyPermission(['create_clients', 'manage_clients']);
    // }

    // public function update(User $user, Client $client): bool
    // {
    //     // Les admins et actuaires peuvent modifier tous les clients
    //     if ($user->hasRole('admin') || $user->hasRole('actuaire')) {
    //         return true;
    //     }

    //     // Les agents ne peuvent modifier que leurs clients
    //     if ($user->hasRole('agent')) {
    //         return $client->agent_id === $user->agent->id;
    //     }

    //     // Les clients ne peuvent modifier que leur propre profil
    //     if ($user->hasRole('client')) {
    //         return $client->utilisateur_id === $user->id;
    //     }

    //     return false;
    // }

    // public function delete(User $user, Client $client): bool
    // {
    //     // Seuls les admins peuvent supprimer des clients
    //     return $user->hasRole('admin') && $user->hasPermissionTo('delete_clients');
    // }

    // public function restore(User $user, Client $client): bool
    // {
    //     return $user->hasRole('admin') && $user->hasPermissionTo('restore_clients');
    // }

    // public function forceDelete(User $user, Client $client): bool
    // {
    //     return $user->hasRole('admin') && $user->hasPermissionTo('force_delete_clients');
    // }

    // public function verifyKyc(User $user, Client $client): bool
    // {
    //     return $user->hasAnyPermission(['verify_kyc', 'manage_clients']);
    // }

    // public function assignAgent(User $user, Client $client): bool
    // {
    //     return $user->hasAnyPermission(['assign_agent', 'manage_clients']);
    // }

    // public function viewFinancial(User $user, Client $client): bool
    // {
    //     // Admins, actuaires et agents assignés peuvent voir les infos financières
    //     if ($user->hasRole('admin') || $user->hasRole('actuaire')) {
    //         return true;
    //     }

    //     if ($user->hasRole('agent')) {
    //         return $client->agent_id === $user->agent->id;
    //     }

    //     return false;
    // }
}
