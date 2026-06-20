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
    $response = $gateway->checkout([
        'amount' => '100.00',
        'currency' => 'TRY',
        'basketId' => 'order_' . uniqid(),
        'returnUrl' => 'http://localhost:8000/checkout-callback.php',
        'enabledInstallments' => [2, 3, 6, 9],
    ])->send();

    if ($response->isRedirect()) {
        echo "Iyzico odeme sayfasina yonlendiriliyorsunuz...\n";
        echo "URL: " . $response->getRedirectUrl() . "\n";

        $response->redirect();
    } else {
        echo "Checkout baslatilamadi: " . $response->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
