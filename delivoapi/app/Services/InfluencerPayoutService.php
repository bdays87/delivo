<?php

namespace App\Services;

use App\Interfaces\Repositories\IInfluencerEarningInterface;
use App\Interfaces\Repositories\IPayoutRequestInterface;
use App\Models\Influencer;
use App\Models\PayoutRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Influencer-facing payout flow:
 *   - summary(): pending / cleared / paid balances + min withdrawal config
 *   - listRequests(): the influencer's own request history
 *   - request(): turn cleared ledger entries into a PENDING payout request
 *     (locks the entries until the request is paid or rejected)
 *   - cancel(): rescind a PENDING request before admin touches it
 */
class InfluencerPayoutService
{
    public function __construct(
        private readonly IInfluencerEarningInterface $earnings,
        private readonly IPayoutRequestInterface $payouts,
    ) {}

    public function summary(Influencer $influencer): array
    {
        return [
            'pending_usd' => round($this->earnings->pendingTotal($influencer->id), 2),
            'cleared_usd' => round($this->earnings->clearedTotal($influencer->id), 2),
            'paid_usd' => round($this->earnings->paidTotal($influencer->id), 2),
            'min_payout_usd' => (float) config('influencer.min_payout_usd'),
            'service_fee_pct' => (float) config('influencer.payout_service_fee_pct'),
        ];
    }

    public function listEarnings(Influencer $influencer): Collection
    {
        return $this->earnings->listForInfluencer($influencer->id);
    }

    public function listRequests(Influencer $influencer): Collection
    {
        return $this->payouts->listForInfluencer($influencer->id);
    }

    public function request(Influencer $influencer, array $data): array
    {
        $cleared = round($this->earnings->clearedTotal($influencer->id), 2);
        $min = (float) config('influencer.min_payout_usd');

        if ($cleared <= 0) {
            return ['error' => 'You have no cleared earnings to withdraw.', 'code' => 422];
        }
        if ($cleared < $min) {
            return [
                'error' => sprintf('Minimum payout is %.2f USD. You have %.2f cleared.', $min, $cleared),
                'code' => 422,
            ];
        }

        // Only one open request at a time — keeps the ledger lock simple.
        if ($this->payouts->pendingForInfluencer($influencer->id)->isNotEmpty()) {
            return [
                'error' => 'You already have a pending payout request. Wait for it to be processed.',
                'code' => 409,
            ];
        }

        $feePct = (float) config('influencer.payout_service_fee_pct');
        $feeUsd = round($cleared * ($feePct / 100), 2);
        $netUsd = round($cleared - $feeUsd, 2);

        return DB::transaction(function () use ($influencer, $data, $cleared, $feePct, $feeUsd, $netUsd) {
            $request = PayoutRequest::query()->create([
                'influencer_id' => $influencer->id,
                'requested_usd' => $cleared,
                'service_fee_pct' => $feePct,
                'service_fee_usd' => $feeUsd,
                'net_payout_usd' => $netUsd,
                'status' => PayoutRequest::STATUS_PENDING,
                'method' => $data['method'],
                'destination_label' => $data['destination_label'] ?? null,
                'destination_account' => $data['destination_account'],
                'influencer_notes' => $data['notes'] ?? null,
            ]);

            $this->earnings->lockClearedToPayout($influencer->id, $request->id);

            return ['request' => $request->fresh()];
        });
    }

    public function cancel(Influencer $influencer, int $requestId): array
    {
        $request = $this->payouts->findForInfluencer($requestId, $influencer->id);
        if ($request === null) {
            return ['error' => 'Payout request not found.', 'code' => 404];
        }
        if ($request->status !== PayoutRequest::STATUS_PENDING) {
            return ['error' => 'Only pending requests can be cancelled.', 'code' => 422];
        }

        DB::transaction(function () use ($request) {
            $this->earnings->unlockPayout($request->id);
            $request->forceFill(['status' => PayoutRequest::STATUS_REJECTED])->save();
            $request->forceFill(['rejection_reason' => 'Cancelled by influencer.'])->save();
        });

        return ['request' => $request->fresh()];
    }
}
