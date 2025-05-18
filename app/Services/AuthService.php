<?php
namespace App\Services;

use App\DTOs\Auth\RegisterDTO;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private OtpService $otpService
    ) {}

    public function register(RegisterDTO $dto): void
    {
        $this->userRepository->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'device_token' => $dto->device_token,
            'password' => Hash::make($dto->password),
        ]);

        $this->otpService->send($dto->email, 'account_activation');
    }

    public function activateAccount(string $email): void
    {
        $this->userRepository->markEmailVerified($email);
        $this->otpService->delete($email, 'account_activation');
    }

    public function requestPasswordReset(string $email): void
    {
        $this->otpService->send($email, 'password_reset');
    }

    public function resetPassword(string $email, string $newPassword): void
    {
        $this->userRepository->updatePassword($email, Hash::make($newPassword));
        $this->otpService->delete($email, 'password_reset');
    }
    public function getUserByEmail(string $email)
    {
        return $this->userRepository->findByEmail($email);
    }

}
