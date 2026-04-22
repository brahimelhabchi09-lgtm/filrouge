<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Domain\User\Repository\UserRepository as UserRepositoryInterface;
use App\Domain\User\Entity\User as DomainUser;
use App\Infrastructure\Persistence\Eloquent\Model\User as EloquentUser;

class UserRepository implements UserRepositoryInterface
{
    public function create(DomainUser $user): DomainUser
    {
        // 1. Convert Domain Entity -> Eloquent Array
        $eloquentUser = EloquentUser::create($user->toArray());

        // 2. Return Domain Entity (Rehydrated from DB result)
        return new DomainUser(
            $eloquentUser->id,
            $eloquentUser->first_name,
            $eloquentUser->last_name,
            $eloquentUser->email,
            $eloquentUser->password,
            $eloquentUser->role
        );
    }
}
