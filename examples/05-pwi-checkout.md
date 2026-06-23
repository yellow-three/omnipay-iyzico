# Pay with iyzico (PWI)

Pay with iyzico is an alternative payment method where the user completes payment on iyzico's hosted page.

## Initialize PWI Payment

```php
$response = $gateway->payWithIyzico([
    'amount' => '100.00',
    'currency' => 'TRY',
    'basketId' => 'order_123',
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
    'buyer' => [
        'id' => 'user_123',
        'name' => 'John',
        'surname' => 'Doe',
        'email' => 'john@example.com',
    ],
    'shippingAddress' => [
        'address' => 'Address line 1',
        'city' => 'Istanbul',
        'country' => 'Turkey',
    ],
    'billingAddress' => [
        'address' => 'Address line 1',
        'city' => 'Istanbul',
        'country' => 'Turkey',
    ],
    'basketItems' => [
        ['id' => 'item_1', 'name' => 'Product 1', 'price' => '50.00', 'category' => 'Electronics'],
        ['id' => 'item_2', 'name' => 'Product 2', 'price' => '50.00', 'category' => 'Clothing'],
    ],
])->send();

if ($response->isRedirect()) {
    // Redirect user to iyzico PWI page
    return $response->redirect();
}
```

## Retrieve PWI Payment Status

After the user completes payment on iyzico's page, retrieve the result:

```php
$response = $gateway->payWithIyzicoStatus([
    'token' => 'pwi_token_from_callback',
])->send();

if ($response->isSuccessful()) {
    echo 'Payment ID: ' . $response->getPaymentId();
    echo 'Status: ' . $response->getPaymentStatus();
}
```

## Checkout (Pay with iyzico CheckoutForm)

Alternative: Use iyzico's checkout form (embedded iframe):

```php
$response = $gateway->checkout([
    'amount' => '100.00',
    'currency' => 'TRY',
    'basketId' => 'order_123',
    'returnUrl' => 'https://yoursite.com/callback',
    'enabledInstallments' => [2, 3, 6, 9],
])->send();

if ($response->isRedirect()) {
    // Render checkout form
    echo $response->getCheckoutFormContent();
}

// After callback:
$response = $gateway->checkoutStatus([
    'token' => 'token_from_callback',
])->send();
```

---

*See also: [01-basic-payment.md](./01-basic-payment.md) for basic payment operations.*
