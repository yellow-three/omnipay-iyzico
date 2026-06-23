# 3D Secure Flow — End to End

This guide walks through a complete 3D Secure payment.

## Step 1: Initialize

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Iyzico');
$gateway->setApiKey('your-api-key');
$gateway->setSecretKey('your-secret-key');
$gateway->setTestMode(true);
```

## Step 2: Purchase with 3DS

```php
$response = $gateway->purchase([
    'amount' => '250.00',
    'currency' => 'TRY',
    'installment' => 1,
    'secure3d' => true,
    'returnUrl' => 'https://yoursite.com/payment/callback',
    'card' => [
        'number' => '4543590000000006',
        'expiryMonth' => '12',
        'expiryYear' => '2030',
        'cvv' => '123',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '+905551112233',
    ],
    'description' => 'Order #123',
])->send();

if ($response->isRedirect()) {
    // Render the HTML form (auto-submits to bank 3DS page)
    echo $response->getHtmlContent();
    exit;
}
```

## Step 3: Callback Route

User completes 3DS authentication and is redirected back.

```php
// Route: POST /payment/callback
// iyzico sends form-encoded POST data

$response = $gateway->completePurchase($_POST)->send();

if ($response->isSuccessful()) {
    echo 'Payment OK. ID: ' . $response->getTransactionReference();
} else {
    echo 'Payment failed: ' . $response->getMessage();
}
```

## Step 4: Webhook (Optional)

iyzico also sends a webhook for async confirmation.

```php
// Route: POST /payment/webhook
$payload = json_decode(file_get_contents('php://input'), true);

$response = $gateway->acceptNotification($payload)->send();

if ($response->isValid() && $response->getTransactionStatus() === 'completed') {
    // Update order status
    $orderId = $response->getTransactionReference();
}
```

## Important Notes

| Note | Detail |
|------|--------|
| Callback method | iyzico sends **POST**, not GET. Your route must accept POST. |
| CSRF | No CSRF token in iyzico callbacks — exempt the route. |
| conversationData | May be `null` even on success — check `mdStatus=1` + `status=success` as fallback. |
| conversationData null | Skip `completePurchase()` and redirect directly to status page. |
| Test 3DS password | `283126` |
| Test card | `4543590000000006`, expiry `12/2030`, CVV `123` |

---

*See also: [01-basic-payment.md](./01-basic-payment.md) for basic payment operations.*
