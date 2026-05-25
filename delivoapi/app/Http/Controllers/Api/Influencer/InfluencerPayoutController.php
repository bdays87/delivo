<?php

namespace App\Http\Controllers\Api\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Influencer\StorePayoutRequestRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Influencer;
use App\Services\InfluencerPayoutService;
use App\Services\InfluencerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfluencerPayoutController extends Controller
{
    public function __construct(
        private readonly InfluencerService $influencers,
        private readonly InfluencerPayoutService $payouts,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $influencer = $this->activeInfluencer($request);
        if (! $influencer instanceof Influencer) {
            return $influencer;
        }

        return ApiResponse::success(
            $this->payouts->summary($influencer),
            'Earnings summary retrieved successfully.',
        );
    }

    public function earnings(Request $request): JsonResponse
    {
        $influencer = $this->activeInfluencer($request);
        if (! $influencer instanceof Influencer) {
            return $influencer;
        }

        return ApiResponse::success(
            $this->payouts->listEarnings($influencer),
            'Earnings retrieved successfully.',
        );
    }

    public function index(Request $request): JsonResponse
    {
        $influencer = $this->activeInfluencer($request);
        if (! $influencer instanceof Influencer) {
            return $influencer;
        }

        return ApiResponse::success(
            $this->payouts->listRequests($influencer),
            'Payout requests retrieved successfully.',
        );
    }

    public function store(StorePayoutRequestRequest $request): JsonResponse
    {
        $influencer = $this->activeInfluencer($request);
        if (! $influencer instanceof Influencer) {
            return $influencer;
        }

        $result = $this->payouts->request($influencer, $request->validated());
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::created($result['request'], 'Payout request submitted.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $influencer = $this->activeInfluencer($request);
        if (! $influencer instanceof Influencer) {
            return $influencer;
        }

        $result = $this->payouts->cancel($influencer, $id);
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::success($result['request'], 'Payout request cancelled.');
    }

    private function activeInfluencer(Request $request): Influencer|JsonResponse
    {
        $influencer = $this->influencers->currentForUser($request->user());
        if ($influencer === null) {
            return ApiResponse::notFound('No influencer application on file.');
        }
        if ($influencer->status !== Influencer::STATUS_ACTIVE) {
            return ApiResponse::forbidden('Your influencer profile must be approved before requesting payouts.');
        }

        return $influencer;
    }
}
