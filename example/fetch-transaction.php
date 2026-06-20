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
    $response = $gateway->fetchTransaction([
        'paymentId' => 'pay_123456',
        'conversationId' => 'query_' . uniqid(),
    ])->send();

    if ($response->isSuccessful()) {
        echo "Odeme detaylari:\n";
        echo "  Status: " . $response->getStatus() . "\n";
        echo "  Payment ID: " . $response->getPaymentId() . "\n";
        echo "  Payment Status: " . $response->getPaymentStatus() . "\n";
        echo "  Conversation ID: " . $response->getConversationId() . "\n";

        $data = $response->getData();
        if (isset($data['price'])) {
            echo "  Amount: " . $data['price'] . " " . ($data['currency'] ?? 'TRY') . "\n";
        }
        if (isset($data['paymentItems'])) {
            echo "  Items: " . count($data['paymentItems']) . "\n";
        }
    } else {
        echo "Sorgulama basarisiz: " . $response->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
