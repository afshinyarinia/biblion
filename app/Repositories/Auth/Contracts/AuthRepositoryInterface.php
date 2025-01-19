<?php

namespace App\Repositories\Auth\Contracts;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function createToken(User $user, string $name = 'auth_token'): string;
    public function revokeCurrentToken(User $user): void;
} 