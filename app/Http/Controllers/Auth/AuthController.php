<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuthService;
use App\Services\OtpService;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\LoginDTO;
use App\Traits\ApiResponseTrait;
use App\Services\NotificationService;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AuthService $authService,
        private OtpService $otpService,
        private NotificationService $notificationService
    ) {}

    // تسجيل مستخدم جديد
    public function register(RegisterRequest $request)
    {
        $dto = new RegisterDTO($request->validated());

        // نسجل المستخدم
        $this->authService->register($dto);

        // نجيب المستخدم اللي اتسجل (ممكن تضيف دالة في AuthService للبحث حسب الإيميل)
        $user = $this->authService->getUserByEmail($dto->email);

        // نبعت إشعار للمستخدم إذا كان موجود
        if ($user) {
            $this->notificationService->notifyUser(
                userId: $user->id,
                deviceToken: $dto->device_token,
                title: 'تم التسجيل بنجاح',
                body: 'لقد قمت بالتسجيل بنجاح في منصتنا.'
            );
        }

        return $this->successResponse(null, 'تم التسجيل بنجاح، تم إرسال رمز التحقق.', 201);
    }


    // تسجيل دخول
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return $this->errorResponse('بيانات الدخول غير صحيحة.', 401);
        }

        $user = Auth::user();

        if (is_null($user->email_verified_at)) {
            return $this->errorResponse('البريد الإلكتروني غير مفعل.', 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            ['token' => $token, 'user' => $user],
            'تم تسجيل الدخول بنجاح.'
        );
    }

    // إرسال رمز التحقق OTP
    public function sendOtp(SendOtpRequest $request)
    {
        $this->otpService->send($request->email, $request->type);

        return $this->successResponse(null, 'تم إرسال رمز التحقق.');
    }

    // التحقق من رمز التحقق OTP
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $valid = $this->otpService->verify($request->email, $request->type, $request->otp);

        if (! $valid) {
            return $this->errorResponse('رمز التحقق غير صالح أو منتهي.', 422);
        }

        if ($request->type === 'account_activation') {
            $this->authService->activateAccount($request->email);
        }

        return $this->successResponse(null, 'تم التحقق من رمز التحقق بنجاح.');
    }

    // إعادة تعيين كلمة المرور
    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->authService->resetPassword($request->email, $request->password);

        return $this->successResponse(null, 'تم إعادة تعيين كلمة المرور بنجاح.');
    }

    // تسجيل خروج
    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'تم تسجيل الخروج بنجاح.');
    }
}
