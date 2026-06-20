<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Omnipay\Omnipay;

// Sandbox test kartlari
$testCards = [
    'Basarili Visa' => '4111111111111111',
    'Basarisiz Visa' => '4000000000000002',
    'Basarili Mastercard' => '5528790000000008',
    'Basarisiz Mastercard' => '5100000000000015',
    'Threeds Visa' => '4111111111111111',
];

echo "=== Iyzico Sandbox Test Kartlari ===\n\n";

foreach ($testCards as $name => $number) {
    echo "$name: $number\n";
}

echo "\n=== Test Odeme Ornegi ===\n\n";

$gateway = Omnipay::create('Iyzico');
$gateway->setApiKey('sandbox-api-key');
$gateway->setSecretKey('sandbox-secret-key');
$gateway->setBaseUrl('https://sandbox-api.iyzipay.com');
$gateway->setIdentityNumber('11111111111');
$gateway->setLocale('TR');
$gateway->setSecure3d(false);
$gateway->setTestMode(true);

$response = $gateway->purchase([
    'amount' => '1.00',
    'currency' => 'TRY',
    'card' => [
        'number' => $testCards['Basarili Visa'],
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
    echo "Test odeme basarili!\n";
    echo "Payment ID: " . $response->getPaymentId() . "\n";
} else {
    echo "Test odeme basarisiz: " . $response->getMessage() . "\n";
}

echo "\n=== Ortam Ayarlari ===\n";
echo "Sandbox URL: https://sandbox-api.iyzipay.com\n";
echo "Production URL: https://api.iyzipay.com\n";
echo "Test kartlari: https://dev.iyzipay.com/tr/test-kartlari\n";
echo "3DS sifresi: 283126\n";
