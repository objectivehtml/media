<?php

namespace Objectivehtml\Media\Policies;

use Objectivehtml\Media\Model;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class MediaPolicy
{
    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
    * Determine if the media collection can be shown to the user.
    *
    * @param  \App\User  $user
    * @param  \App\Post  $post
    * @return bool
    */
    public function index(Authenticatable $user)
    {
        return !!$user;
    }

    /**
    * Determine if the given media can be updated by the user.
    *
    * @param  \App\User  $user
    * @param  \App\Post  $post
    * @return bool
    */
    public function create(Authenticatable $user)
    {
        return !!$user;
    }

    /**
    * Determine if the given media can be shown to the user.
    *
    * @param  \App\User  $user
    * @param  \App\Post  $post
    * @return bool
    */
    public function view(Authenticatable $user, Media $model)
    {
        return $user->id === $model->user_id;
    }

    /**
    * Determine if the given media can be updated by the user.
    *
    * @param  \App\User  $user
    * @param  \App\Post  $post
    * @return bool
    */
    public function update(Authenticatable $user, Media $model)
    {
        return $user->id === $model->user_id;
    }

    /**
    * Determine if the given media can be deleted by the user.
    *
    * @param  \App\User  $user
    * @param  \App\Post  $post
    * @return bool
    */
    public function delete(Authenticatable $user, Media $model)
    {
        return $user->id === $model->user_id;
    }

}
