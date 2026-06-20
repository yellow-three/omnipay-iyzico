# Omnipay: Iyzico

**Iyzico gateway for Omnipay v3 payment processing library**

[![Latest Stable Version](https://poser.pugx.org/yellow-three/omnipay-iyzico/v/stable)](https://packagist.org/packages/yellow-three/omnipay-iyzico)
[![Total Downloads](https://poser.pugx.org/yellow-three/omnipay-iyzico/downloads)](https://packagist.org/packages/yellow-three/omnipay-iyzico)
[![License](https://poser.pugx.org/yellow-three/omnipay-iyzico/license)](https://packagist.org/packages/yellow-three/omnipay-iyzico)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment processing library for PHP. This package implements Iyzico support for Omnipay v3.

## Installation

```bash
composer require yellow-three/omnipay-iyzico
```

## Basic Usage

### Initialize Gateway

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Iyzico');

$gateway->setApiKey('your-api-key');
$gateway->setSecretKey('your-secret-key');
$gateway->setBaseUrl('https://sandbox-api.iyzipay.com'); // Sandbox
$gateway->setIdentityNumber('11111111111'); // Buyer identity number
$gateway->setTestMode(true);
```

### Purchase (3D Secure)

```php
$response = $gateway->purchase([
    'amount' => '100.00',
    'currency' => 'TRY',
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
    'returnUrl' => 'https://yoursite.com/payment/callback',
    'secure3d' => true,
    'description' => 'Order #123',
])->send();

if ($response->isRedirect()) {
    // Redirect to 3DS page
    $response->redirect();
} elseif ($response->isSuccessful()) {
    // Payment successful
    echo $response->getTransactionReference();
} else {
    echo $response->getMessage();
}
```

### Purchase (Non-3D Secure)

```php
$response = $gateway->purchase([
    'amount' => '100.00',
    'currency' => 'TRY',
    'card' => $cardData,
    'secure3d' => false,
])->send();
```

### Authorize

```php
$response = $gateway->authorize([
    'amount' => '100.00',
    'currency' => 'TRY',
    'card' => $cardData,
    'secure3d' => true,
    'returnUrl' => 'https://yoursite.com/payment/callback',
])->send();
```

### Capture

```php
$response = $gateway->capture([
    'paymentId' => 'pay_abc123',
    'amount' => '100.00',
    'conversationId' => 'conv_123',
])->send();
```

### Refund

```php
$response = $gateway->refund([
    'paymentTransactionId' => 'tx_abc123',
    'amount' => '50.00',
    'currency' => 'TRY',
    'conversationId' => 'conv_123',
])->send();
```

### Void

```php
$response = $gateway->void([
    'paymentId' => 'pay_abc123',
    'conversationId' => 'conv_123',
])->send();
```

### Fetch Transaction

```php
$response = $gateway->fetchTransaction([
    'paymentId' => 'pay_abc123',
    'conversationId' => 'conv_123',
])->send();

$paymentId = $response->getPaymentId();
$status = $response->getPaymentStatus();
```

### Checkout (Pay with iyzico)

```php
$response = $gateway->checkout([
    'amount' => '100.00',
    'currency' => 'TRY',
    'basketId' => 'order_123',
    'returnUrl' => 'https://yoursite.com/payment/callback',
    'enabledInstallments' => [2, 3, 6, 9],
])->send();

if ($response->isRedirect()) {
    $response->redirect();
}
```

### Checkout Status

```php
$response = $gateway->checkoutStatus([
    'token' => 'token_from_callback',
    'conversationId' => 'conv_123',
])->send();
```

## Gateway Parameters

| Parameter | Type | Default | Description |
|---|---|---|---|
| `apiKey` | string | `''` | iyzico API key |
| `secretKey` | string | `''` | iyzico Secret key |
| `baseUrl` | string | `sandbox-api.iyzipay.com` | API base URL |
| `testMode` | bool | `false` | Enable sandbox mode |
| `locale` | string | `TR` | `TR` or `EN` |
| `currency` | string | `TRY` | TRY, USD, EUR, GBP, RUB, AZN, KWD, SAR, EGP, JOD, AED, BHD, QAR |
| `secure3d` | bool | `true` | Enable 3D Secure |
| `installment` | int | `0` | Installment count |
| `identityNumber` | string | `''` | Buyer TCKN |
| `paymentChannel` | string | `WEB` | WEB, MOBILE, WEB_POS |
| `paymentGroup` | string | `PRODUCT` | PRODUCT, INHERITED, SUBSCRIPTION |

## Sandbox Testing

1. Register at [sandbox-merchant.iyzipay.com](https://sandbox-merchant.iyzipay.com/auth)
2. Login with SMS code `123456`
3. Get API keys from Settings > API Keys
4. Use test cards from [dev.iyzipay.com/tr/test-kartlari](https://dev.iyzipay.com/tr/test-kartlari)
5. 3DS password: `283126`

## Requirements

- PHP >= 8.1
- Omnipay Common v3
- iyzico/iyzipay-php v2

## License

MIT License. See [LICENSE](LICENSE) for details.
