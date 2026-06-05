<?php

$secret = 'super-secret-key';

$body = file_get_contents('php://input');

$signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';

$calculated = hash_hmac(
    'sha256',
    $body,
    $secret
);

if (!hash_equals($calculated, $signature)) {

    http_response_code(401);

    echo json_encode([
        'error' => 'Invalid signature',
    ]);

    exit;
}

$idempotencyKey = $_SERVER['HTTP_IDEMPOTENCY_KEY'] ?? '';

$storageFile = __DIR__ . '/processed_keys.json';

if (!file_exists($storageFile)) {
    file_put_contents($storageFile, json_encode([]));
}

$processed = json_decode(
    file_get_contents($storageFile),
    true
);

if (!is_array($processed)) {
    $processed = [];
}

if (in_array($idempotencyKey, $processed, true)) {

    http_response_code(200);

    echo json_encode([
        'duplicate' => true,
    ]);

    exit;
}

$processed[] = $idempotencyKey;

file_put_contents(
    $storageFile,
    json_encode($processed)
);

http_response_code(rand(0, 1) ? 200 : 500);

file_put_contents(
    __DIR__ . '/requests.log',
    $body . PHP_EOL,
    FILE_APPEND
);

echo json_encode([
    'received' => true,
]);
