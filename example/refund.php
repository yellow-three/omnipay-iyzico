<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Omnipay\Omnipay;

$gateway = Omnipay::create('Iyzico');

$gateway->setApiKey('sandbox-api-key');
$gateway->setSecretKey('sandbox-secret-key');
$gateway->setBaseUrl('https://sandbox-api.iyzipay.com');
$gateway->setIdentityNumber('11111111111');
$gateway->setLocale('TR');
$gateway->setTestMode(true);

try {
    $response = $gateway->refund([
        'paymentTransactionId' => 'txn_123456',
        'amount' => '50.00',
        'currency' => 'TRY',
        'conversationId' => 'refund_' . uniqid(),
    ])->send();

    if ($response->isSuccessful()) {
        echo "Iade basarili!\n";
        echo "Status: " . $response->getStatus() . "\n";
        echo "Conversation ID: " . $response->getConversationId() . "\n";
    } else {
        echo "Iade basarisiz: " . $response->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
