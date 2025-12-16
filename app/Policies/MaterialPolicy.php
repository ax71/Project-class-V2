<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }


    public function view(User $user, Material $material): bool
    {
        return true;
    }


    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }


    public function update(User $user, Material $material): bool
    {
        return $user->id === $material->course->user_id || $user->role === 'admin';
    }

    
    public function delete(User $user, Material $material): bool
    {
        return $user->id === $material->course->user_id || $user->role === 'admin';
    }
}
