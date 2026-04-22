<?php

namespace App\Domain\User\Entity;

class User
{
    public function __construct(
        private ?int $id,
        private string $firstName,
        private string $lastName,
        private string $email,
        private string $password,
        private ?string $role = null
    ) {}

    // Getters...
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
        ];
    }
}