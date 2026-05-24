<?php

namespace App\Http\Controllers\Api\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Influencer\InfluencerApplyRequest;
use App\Http\Requests\Influencer\InfluencerSocialHandleRequest;
use App\Http\Responses\ApiResponse;
use App\Services\InfluencerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfluencerController extends Controller
{
    public function __construct(private readonly InfluencerService $service) {}

    public function apply(InfluencerApplyRequest $request): JsonResponse
    {
        $influencer = $this->service->apply($request->user(), $request->validated());

        if ($influencer === null) {
            return ApiResponse::error('You already have an influencer application on file.', 409);
        }

        return ApiResponse::created($influencer->load('socialHandles'), 'Application submitted.');
    }

    public function currentInfluencer(Request $request): JsonResponse
    {
        $influencer = $this->service->currentForUser($request->user());
        if ($influencer === null) {
            return ApiResponse::notFound('No influencer application on file.');
        }

        return ApiResponse::success($influencer, 'Influencer retrieved successfully.');
    }

    public function addHandle(InfluencerSocialHandleRequest $request): JsonResponse
    {
        $influencer = $this->service->currentForUser($request->user());
        if ($influencer === null) {
            return ApiResponse::notFound('Submit your application before adding handles.');
        }

        $handle = $this->service->addHandle($influencer, $request->validated());

        return ApiResponse::created($handle, 'Social handle added.');
    }

    public function deleteHandle(Request $request, int $handleId): JsonResponse
    {
        $influencer = $this->service->currentForUser($request->user());
        if ($influencer === null) {
            return ApiResponse::notFound('No influencer application on file.');
        }

        $handle = $this->service->findHandleForInfluencer($handleId, $influencer->id);
        if ($handle === null) {
            return ApiResponse::notFound('Social handle not found.');
        }

        $this->service->removeHandle($handle);

        return ApiResponse::success([], 'Social handle removed.');
    }
}
