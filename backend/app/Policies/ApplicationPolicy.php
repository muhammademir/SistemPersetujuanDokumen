<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use App\Enums\ApplicationStatus;
use Illuminate\Auth\Access\Response;

class ApplicationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Application $application): bool
    {
        if ($this->isOwner($user, $application)) {
            return true;
        }
        return $user->hasRole('penilai') && $application->status !== ApplicationStatus::DRAFT;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('pemohon');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Application $application): bool
    {
        return $this->isOwner($user, $application);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Application $application): bool
    {
        return $this->isOwner($user, $application);
    }

    public function submit(User $user, Application $application): bool
    {
        return $this->isOwner($user, $application);
    }

    private function isOwner(User $user, Application $application): bool
    {
        return (int) $user->id === (int) $application->applicant_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Application $application): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Application $application): bool
    {
        return false;
    }
}
