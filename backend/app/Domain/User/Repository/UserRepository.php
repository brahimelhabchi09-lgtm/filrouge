<?php

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;

interface UserRepository
{
    public function create(User $user): User;
}
