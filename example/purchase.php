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

try {
    $response = $gateway->purchase([
        'amount' => '100.00',
        'currency' => 'TRY',
        'description' => 'Test siparis #001',
        'returnUrl' => 'http://localhost:8000/callback.php',
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
        'items' => [
            [
                'name' => 'Ürün A',
                'description' => 'Elektronik',
                'price' => '60.00',
            ],
            [
                'name' => 'Ürün B',
                'description' => 'Giyim',
                'price' => '40.00',
            ],
        ],
    ])->send();

    if ($response->isRedirect()) {
        echo "3DS Dogrulamaya yonlendiriliyorsunuz...\n";
        echo "Redirect URL: " . $response->getRedirectUrl() . "\n";

        $html = $response->getCheckoutFormContent() ?? '';
        if ($html) {
            file_put_contents(__DIR__ . '/3ds-form.html', $html);
            echo "3DS formu 3ds-form.html olarak kaydedildi.\n";
        }
    } elseif ($response->isSuccessful()) {
        echo "Odeme basarili!\n";
        echo "Payment ID: " . $response->getPaymentId() . "\n";
        echo "Transaction Reference: " . $response->getTransactionReference() . "\n";
    } else {
        echo "Odeme basarisiz: " . $response->getMessage() . "\n";
        echo "Status: " . $response->getStatus() . "\n";
    }
} catch (\Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
