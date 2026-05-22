<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPlatformSettingsRequest;
use App\Http\Responses\ApiResponse;
use App\Interfaces\Repositories\IPlatformSettingsInterface;
use Illuminate\Http\JsonResponse;

class AdminPlatformSettingsController extends Controller
{
    public function __construct(private readonly IPlatformSettingsInterface $repo) {}

    public function show(): JsonResponse
    {
        return ApiResponse::success($this->repo->current(), 'Platform settings retrieved.');
    }

    public function update(AdminPlatformSettingsRequest $request): JsonResponse
    {
        $saved = $this->repo->save($request->validated());

        return ApiResponse::success($saved, 'Platform settings updated.');
    }
}
