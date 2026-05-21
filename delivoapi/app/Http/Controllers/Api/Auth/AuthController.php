<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $service) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->service->register($request->validated());

        return ApiResponse::success(
            ['user' => $result['user']],
            'Registration successful.',
            201,
            $result['token'],
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->service->login($request->validated());

        if ($result === null) {
            return ApiResponse::unauthorized('Invalid email or password.');
        }

        return ApiResponse::success(
            ['user' => $result['user']],
            'Login successful.',
            200,
            $result['token'],
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->service->logout($request->user());

        return ApiResponse::success([], 'Logged out.');
    }

    /**
     * Identity endpoint. Returns the user at the response root (no
     * ApiResponse envelope) so nuxt-auth-sanctum can assign it directly
     * to its reactive user state.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($this->service->presentUser($request->user()));
    }
}
