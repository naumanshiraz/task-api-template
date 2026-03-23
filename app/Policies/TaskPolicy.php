<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine if the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->project->user_id;
    }

    /**
     * Determine if the user can create a task.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Determine if the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->project->user_id;
    }

    /**
     * Determine if the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->project->user_id;
    }
}