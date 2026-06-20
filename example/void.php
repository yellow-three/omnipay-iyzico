<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Omnipay\Omnipay;

$gateway = Omnipay::create('Iyzico');

$gateway->setApiKey('sandbox-api-key');
$gateway->setSecretKey('sandbox-secret-key');
$gateway->setBaseUrl('https://sandbox-api.iyzipay.com');
$gateway->setLocale('TR');
$gateway->setTestMode(true);

try {
    $response = $gateway->void([
        'paymentId' => 'pay_123456',
        'conversationId' => 'void_' . uniqid(),
    ])->send();

    if ($response->isSuccessful()) {
        echo "Iptal basarili!\n";
        echo "Status: " . $response->getStatus() . "\n";
    } else {
        echo "Iptal basarisiz: " . $response->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
