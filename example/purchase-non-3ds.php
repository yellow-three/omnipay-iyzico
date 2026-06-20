<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Omnipay\Omnipay;

$gateway = Omnipay::create('Iyzico');

$gateway->setApiKey('sandbox-api-key');
$gateway->setSecretKey('sandbox-secret-key');
$gateway->setBaseUrl('https://sandbox-api.iyzipay.com');
$gateway->setIdentityNumber('11111111111');
$gateway->setLocale('TR');
$gateway->setSecure3d(false);
$gateway->setTestMode(true);

try {
    $response = $gateway->purchase([
        'amount' => '100.00',
        'currency' => 'TRY',
        'description' => 'Non-3DS test odeme',
        'card' => [
            'number' => '4111111111111111',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+905551112233',
        ],
    ])->send();

    if ($response->isSuccessful()) {
        echo "Odeme basarili!\n";
        echo "Payment ID: " . $response->getPaymentId() . "\n";
        echo "Status: " . $response->getPaymentStatus() . "\n";
        echo "Token: " . $response->getToken() . "\n";
    } else {
        echo "Odeme basarisiz: " . $response->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
