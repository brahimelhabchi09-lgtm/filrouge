<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Model\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Infrastructure\Persistence\Eloquent\Model\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        return [
            'first_name' => 'User' . $counter,
            'last_name' => 'Test',
            'email' => 'user' . $counter . '@youports.com',
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'STUDENT',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'ADMIN',
        ]);
    }

    /**
     * Create a teacher user.
     */
    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'TEACHER',
        ]);
    }

    /**
     * Create a student user.
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'STUDENT',
        ]);
    }

    /**
     * Create a BDE member user.
     */
    public function bde(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'BDE',
        ]);
    }

    /**
     * Set a specific role for the user.
     */
    public function withRole(string $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => strtoupper($role),
        ]);
    }

    /**
     * Set a custom password (hashed).
     */
    public function withPasswordHash(string $password): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Create a user with a specific email.
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }

    /**
     * Create a user with a specific first and last name.
     */
    public function withName(string $firstName, string $lastName): static
    {
        return $this->state(fn (array $attributes) => [
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
    }

    /**
     * Set a static password for all users in the factory sequence.
     * Useful for testing when you need consistent passwords.
     */
    public static function setDefaultPassword(string $password): void
    {
        static::$password = Hash::make($password);
    }

    /**
     * Reset the default password.
     */
    public static function resetDefaultPassword(): void
    {
        static::$password = null;
    }
}
