<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPayoutRejectRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AdminPayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPayoutController extends Controller
{
    public function __construct(private readonly AdminPayoutService $service) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->listByStatus($request->query('status')),
            'Payout requests retrieved successfully.',
        );
    }

    public function show(int $id): JsonResponse
    {
        $request = $this->service->find($id);
        if ($request === null) {
            return ApiResponse::notFound('Payout request not found.');
        }

        return ApiResponse::success($request, 'Payout request retrieved successfully.');
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $payout = $this->service->find($id);
        if ($payout === null) {
            return ApiResponse::notFound('Payout request not found.');
        }
        $result = $this->service->approve($payout, $request->user(), $request->input('notes'));
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::success($result['request'], 'Payout request approved.');
    }

    public function markPaid(Request $request, int $id): JsonResponse
    {
        $payout = $this->service->find($id);
        if ($payout === null) {
            return ApiResponse::notFound('Payout request not found.');
        }
        $result = $this->service->markPaid($payout, $request->user(), $request->input('notes'));
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::success($result['request'], 'Payout marked as paid.');
    }

    public function reject(AdminPayoutRejectRequest $request, int $id): JsonResponse
    {
        $payout = $this->service->find($id);
        if ($payout === null) {
            return ApiResponse::notFound('Payout request not found.');
        }
        $result = $this->service->reject($payout, $request->user(), $request->validated()['reason']);
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::success($result['request'], 'Payout request rejected.');
    }
}
