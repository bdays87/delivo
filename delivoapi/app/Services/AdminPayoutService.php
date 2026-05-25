<?php

namespace App\Services;

use App\Interfaces\Repositories\IInfluencerEarningInterface;
use App\Interfaces\Repositories\IPayoutRequestInterface;
use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Admin-side payout queue.
 *   - PENDING -> APPROVED  (admin queues for payment)
 *   - APPROVED -> PAID    (admin records the payment + marks ledger entries paid)
 *   - PENDING|APPROVED -> REJECTED  (returns ledger entries to CLEARED)
 */
class AdminPayoutService
{
    public function __construct(
        private readonly IPayoutRequestInterface $payouts,
        private readonly IInfluencerEarningInterface $earnings,
    ) {}

    public function listByStatus(?string $status = null): Collection
    {
        return $this->payouts->listByStatus($status);
    }

    public function find(int $id): ?PayoutRequest
    {
        return $this->payouts->findWithRelations($id);
    }

    public function approve(PayoutRequest $request, User $admin, ?string $notes = null): array
    {
        if ($request->status !== PayoutRequest::STATUS_PENDING) {
            return ['error' => 'Only pending requests can be approved.', 'code' => 422];
        }

        $request->forceFill([
            'status' => PayoutRequest::STATUS_APPROVED,
            'admin_notes' => $notes,
            'processed_by_user_id' => $admin->id,
            'processed_at' => now(),
        ])->save();

        return ['request' => $request->fresh()];
    }

    public function markPaid(PayoutRequest $request, User $admin, ?string $notes = null): array
    {
        if (! in_array($request->status, [PayoutRequest::STATUS_PENDING, PayoutRequest::STATUS_APPROVED], true)) {
            return ['error' => 'Only pending or approved requests can be marked paid.', 'code' => 422];
        }

        DB::transaction(function () use ($request, $admin, $notes) {
            $request->forceFill([
                'status' => PayoutRequest::STATUS_PAID,
                'admin_notes' => $notes ?? $request->admin_notes,
                'processed_by_user_id' => $admin->id,
                'processed_at' => $request->processed_at ?? now(),
                'paid_at' => now(),
            ])->save();

            $this->earnings->markPayoutPaid($request->id);
        });

        return ['request' => $request->fresh()];
    }

    public function reject(PayoutRequest $request, User $admin, string $reason): array
    {
        if (! in_array($request->status, [PayoutRequest::STATUS_PENDING, PayoutRequest::STATUS_APPROVED], true)) {
            return ['error' => 'Only pending or approved requests can be rejected.', 'code' => 422];
        }

        DB::transaction(function () use ($request, $admin, $reason) {
            $this->earnings->unlockPayout($request->id);
            $request->forceFill([
                'status' => PayoutRequest::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'processed_by_user_id' => $admin->id,
                'processed_at' => now(),
            ])->save();
        });

        return ['request' => $request->fresh()];
    }
}
