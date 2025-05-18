<?php
namespace App\Repositories;

use App\Models\Otp;

class OtpRepository
{
    public function save(string $email, string $type, string $otp): void
    {
        Otp::updateOrCreate([
            'email' => $email,
            'type' => $type,
        ], [
            'otp' => $otp,
            'created_at' => now(),
        ]);
    }

    public function verify(string $email, string $type, string $otp): bool
    {
        return Otp::where('email', $email)
            ->where('type', $type)
            ->where('otp', $otp)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->exists();
    }

    public function delete(string $email, string $type): void
    {
        Otp::where('email', $email)->where('type', $type)->delete();
    }
}
