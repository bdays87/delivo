<?php

return [
    // Minimum cleared balance before an influencer can request a payout.
    'min_payout_usd' => env('INFLUENCER_MIN_PAYOUT_USD', 10.00),

    // Platform service-fee percentage deducted from the influencer's payout.
    'payout_service_fee_pct' => env('INFLUENCER_PAYOUT_SERVICE_FEE_PCT', 5.00),

    // Six-digit code given to the customer at place-order time. Customer enters
    // it on their order page to confirm delivery, which releases earnings.
    'delivery_code_length' => 6,
];
