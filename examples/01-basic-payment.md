# Basic Payment Examples

## Initialize Gateway

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Iyzico');
$gateway->setApiKey('your-api-key');
$gateway->setSecretKey('your-secret-key');
$gateway->setTestMode(true);
$gateway->setIdentityNumber('11111111111');
```

## Non-3D Secure Purchase

```php
$response = $gateway->purchase([
    'amount' => '100.00',
    'currency' => 'TRY',
    'installment' => 1,
    'card' => [
        'number' => '4543590000000006',
        'expiryMonth' => '12',
        'expiryYear' => '2030',
        'cvv' => '123',
        'firstName' => 'John',
        'lastName' => 'Doe',
    ],
])->send();

if ($response->isSuccessful()) {
    echo 'Payment ID: ' . $response->getTransactionReference();
} else {
    echo 'Error: ' . $response->getMessage();
}
```

## 3D Secure Purchase

```php
$response = $gateway->purchase([
    'amount' => '100.00',
    'currency' => 'TRY',
    'secure3d' => true,
    'returnUrl' => 'https://yoursite.com/callback',
    'card' => [
        'number' => '4543590000000006',
        'expiryMonth' => '12',
        'expiryYear' => '2030',
        'cvv' => '123',
        'firstName' => 'John',
        'lastName' => 'Doe',
    ],
])->send();

if ($response->isRedirect()) {
    // Render 3DS form in browser
    echo $response->getHtmlContent();
}
```

## Authorize (Pre-Authorization)

```php
$response = $gateway->authorize([
    'amount' => '100.00',
    'currency' => 'TRY',
    'card' => $cardData,
    'secure3d' => true,
    'returnUrl' => 'https://yoursite.com/callback',
])->send();
```

## Capture (Post-Authorization)

```php
$response = $gateway->capture([
    'paymentId' => 'pay_abc123',
    'amount' => '100.00',
    'conversationId' => 'conv_123',
])->send();
```

## Refund

```php
$response = $gateway->refund([
    'paymentTransactionId' => 'tx_abc123',
    'amount' => '50.00',
    'currency' => 'TRY',
    'conversationId' => 'conv_123',
])->send();
```

## Void (Cancel)

```php
$response = $gateway->void([
    'paymentId' => 'pay_abc123',
    'conversationId' => 'conv_123',
])->send();
```

## Fetch Transaction

```php
$response = $gateway->fetchTransaction([
    'paymentId' => 'pay_abc123',
    'conversationId' => 'conv_123',
])->send();

echo 'Status: ' . $response->getPaymentStatus();
```

---

*See also: [02-3ds-flow.md](./02-3ds-flow.md) for the full 3DS flow.*
