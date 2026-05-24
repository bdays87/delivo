<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminInfluencerHandleStatusRequest;
use App\Http\Requests\Admin\AdminInfluencerRejectRequest;
use App\Http\Responses\ApiResponse;
use App\Models\InfluencerSocialHandle;
use App\Services\AdminInfluencerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminInfluencerController extends Controller
{
    public function __construct(private readonly AdminInfluencerService $service) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->listByStatus($request->query('status')),
            'Influencers retrieved successfully.',
        );
    }

    public function show(int $id): JsonResponse
    {
        $influencer = $this->service->find($id);
        if ($influencer === null) {
            return ApiResponse::notFound('Influencer not found.');
        }

        return ApiResponse::success($influencer, 'Influencer retrieved successfully.');
    }

    public function approve(int $id): JsonResponse
    {
        $influencer = $this->service->find($id);
        if ($influencer === null) {
            return ApiResponse::notFound('Influencer not found.');
        }
        if (! $this->service->approve($influencer)) {
            return ApiResponse::error('Only pending influencers can be approved.', 409);
        }

        return ApiResponse::success([], 'Influencer approved.');
    }

    public function reject(AdminInfluencerRejectRequest $request, int $id): JsonResponse
    {
        $influencer = $this->service->find($id);
        if ($influencer === null) {
            return ApiResponse::notFound('Influencer not found.');
        }
        if (! $this->service->reject($influencer, $request->validated()['reason'])) {
            return ApiResponse::error('Only pending influencers can be rejected.', 409);
        }

        return ApiResponse::success([], 'Influencer rejected.');
    }

    public function suspend(Request $request, int $id): JsonResponse
    {
        $influencer = $this->service->find($id);
        if ($influencer === null) {
            return ApiResponse::notFound('Influencer not found.');
        }
        if (! $this->service->suspend($influencer, $request->input('reason'))) {
            return ApiResponse::error('Only active influencers can be suspended.', 409);
        }

        return ApiResponse::success([], 'Influencer suspended.');
    }

    public function setHandleStatus(AdminInfluencerHandleStatusRequest $request, int $influencerId, int $handleId): JsonResponse
    {
        $handle = InfluencerSocialHandle::query()
            ->where('id', $handleId)
            ->where('influencer_id', $influencerId)
            ->first();
        if ($handle === null) {
            return ApiResponse::notFound('Social handle not found.');
        }

        $this->service->setHandleStatus($handle, $request->validated()['status']);

        return ApiResponse::success([], 'Social handle updated.');
    }
}
