<?php 

return [
    'minimum_transaction_rows' => env('MINIMUM_TRANSACTION_ROWS', 20),
    'maximum_allowed_queue_size' => env('MAXIMUM_ALLOWED_QUEUE_SIZE', 200)
];