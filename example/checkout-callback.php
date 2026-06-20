<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Omnipay\Omnipay;

if (!isset($_GET['token'])) {
    echo "Token bulunamadi.\n";
    exit(1);
}

$gateway = Omnipay::create('Iyzico');

$gateway->setApiKey('sandbox-api-key');
$gateway->setSecretKey('sandbox-secret-key');
$gateway->setBaseUrl('https://sandbox-api.iyzipay.com');
$gateway->setLocale('TR');
$gateway->setTestMode(true);

try {
    $response = $gateway->checkoutStatus([
        'token' => $_GET['token'],
        'conversationId' => 'checkout_' . uniqid(),
    ])->send();

    if ($response->isSuccessful()) {
        echo "Checkout odemesi basarili!\n";
        echo "Payment ID: " . $response->getPaymentId() . "\n";
        echo "Status: " . $response->getPaymentStatus() . "\n";
    } else {
        echo "Checkout odemesi basarisiz: " . $response->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
