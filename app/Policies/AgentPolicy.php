<?php

namespace App\Policies;

use App\Models\Agent;
use App\Models\User;

class AgentPolicy
{
    // public function viewAny(User $user): bool
    // {
    //     return $user->hasAnyRole(['admin', 'actuaire']) ||
    //         $user->hasPermissionTo('view_agents') ?? true;
    // }

    // public function view(User $user, Agent $agent): bool
    // {
    //     // Admins et actuaires peuvent tout voir
    //     if ($user->hasRole('admin') || $user->hasRole('actuaire')) {
    //         return true;
    //     }

    //     // Agents peuvent voir leur propre profil
    //     if ($user->hasRole('agent')) {
    //         return $user->agent->id === $agent->id;
    //     }

    //     return false;
    // }

    // public function create(User $user): bool
    // {
    //     return $user->hasRole('admin') && $user->hasPermissionTo('create_agents');
    // }

    // public function update(User $user, Agent $agent): bool
    // {
    //     // Admins peuvent modifier tous les agents
    //     if ($user->hasRole('admin')) {
    //         return true;
    //     }

    //     // Agents ne peuvent modifier que leur propre profil
    //     if ($user->hasRole('agent')) {
    //         return $user->agent->id === $agent->id;
    //     }

    //     return false;
    // }

    // public function delete(User $user, Agent $agent): bool
    // {
    //     return $user->hasRole('admin') && $user->hasPermissionTo('delete_agents');
    // }

    // public function viewCommission(User $user, Agent $agent): bool
    // {
    //     // Admins peuvent voir toutes les commissions
    //     if ($user->hasRole('admin')) {
    //         return true;
    //     }

    //     // Agents ne voient que leurs propres commissions
    //     if ($user->hasRole('agent')) {
    //         return $user->agent->id === $agent->id;
    //     }

    //     return false;
    // }

    // public function manageCommission(User $user, Agent $agent): bool
    // {
    //     return $user->hasRole('admin') && $user->hasPermissionTo('manage_commissions');
    // }

    // public function viewPerformance(User $user, Agent $agent): bool
    // {
    //     return $user->hasRole('admin') || $user->hasRole('actuaire') ||
    //         ($user->hasRole('agent') && $user->agent->id === $agent->id);
    // }

}
