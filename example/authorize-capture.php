<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Omnipay\Omnipay;

$gateway = Omnipay::create('Iyzico');

$gateway->setApiKey('sandbox-api-key');
$gateway->setSecretKey('sandbox-secret-key');
$gateway->setBaseUrl('https://sandbox-api.iyzipay.com');
$gateway->setIdentityNumber('11111111111');
$gateway->setLocale('TR');
$gateway->setSecure3d(true);
$gateway->setTestMode(true);

$card = [
    'number' => '4111111111111111',
    'expiryMonth' => '12',
    'expiryYear' => '2030',
    'cvv' => '123',
    'firstName' => 'John',
    'lastName' => 'Doe',
    'email' => 'john@example.com',
    'phone' => '+905551112233',
];

echo "=== 1. Authorize ===\n";
$authResponse = $gateway->authorize([
    'amount' => '100.00',
    'currency' => 'TRY',
    'card' => $card,
    'returnUrl' => 'http://localhost:8000/callback.php',
    'secure3d' => true,
])->send();

if ($authResponse->isSuccessful()) {
    $paymentId = $authResponse->getPaymentId();
    echo "Authorize basarili! Payment ID: $paymentId\n";

    echo "\n=== 2. Capture ===\n";
    $captureResponse = $gateway->capture([
        'paymentId' => $paymentId,
        'amount' => '100.00',
        'conversationId' => 'capture_' . uniqid(),
    ])->send();

    if ($captureResponse->isSuccessful()) {
        echo "Capture basarili!\n";
    } else {
        echo "Capture basarisiz: " . $captureResponse->getMessage() . "\n";
    }
} else {
    echo "Authorize basarisiz: " . $authResponse->getMessage() . "\n";
}
