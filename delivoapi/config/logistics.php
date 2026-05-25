<?php

return [
    // Hours a vendor has to drop a paid order off at the assigned hub before
    // it shows as overdue. No auto-cancel — admin intervenes if a vendor
    // routinely misses windows.
    'dropoff_window_hours' => (int) env('LOGISTICS_DROPOFF_WINDOW_HOURS', 48),
];
