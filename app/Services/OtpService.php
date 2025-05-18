<?php
namespace App\Services;

use App\Repositories\OtpRepository;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function __construct(private OtpRepository $otpRepository) {}

    public function send(string $email, string $type): void
    {
        $otp = rand(100000, 999999);

        $this->otpRepository->save($email, $type, $otp);

        Mail::raw("Your OTP is: $otp", function ($message) use ($email) {
            $message->to($email)->subject("Your OTP Code");
        });
    }


    public function verify(string $email, string $type, string $otp): bool
    {
        return $this->otpRepository->verify($email, $type, $otp);
    }

    public function delete(string $email, string $type): void
    {
        $this->otpRepository->delete($email, $type);
    }
}
