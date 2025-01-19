<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Auth\Contracts\AuthRepositoryInterface;
use App\Services\Auth\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly AuthRepositoryInterface $authRepository
    ) {}

    public function register(array $data): array
    {
        $user = $this->authRepository->create($data);
        $token = $this->authRepository->createToken($user);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }

    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid login credentials'],
            ]);
        }

        $user = $this->authRepository->findByEmail($credentials['email']);
        $token = $this->authRepository->createToken($user);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }

    public function logout(User $user): void
    {
        $this->authRepository->revokeCurrentToken($user);
    }

    public function getCurrentUser(User $user): User
    {
        return $user;
    }
} 