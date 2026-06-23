# Card Storage

Save, list, and delete user cards for future purchases.

## Prerequisites

- `cardUserKey` is a unique identifier for the user (you generate and store it)
- Each saved card gets a `cardToken` — use this for future payments

## Save a Card

```php
$response = $gateway->createCard([
    'card' => [
        'number' => '4543590000000006',
        'expiryMonth' => '12',
        'expiryYear' => '2030',
        'cvv' => '123',
    ],
    'email' => 'user@example.com',
    'cardUserKey' => 'user_key_123',
])->send();

if ($response->isSuccessful()) {
    $cardToken = $response->getCardToken();
    $cardUserKey = $response->getCardUserKey();
    // Store cardToken + cardUserKey for future use
}
```

## List Saved Cards

```php
$response = $gateway->listCards([
    'cardUserKey' => 'user_key_123',
])->send();

if ($response->isSuccessful()) {
    $cards = $response->getCardDetails();
    // Each card has: cardToken, cardAlias, cardBankName, cardAssociation, cardFamily
}
```

## Delete a Saved Card

```php
$response = $gateway->deleteCard([
    'cardToken' => 'card_token_abc',
    'cardUserKey' => 'user_key_123',
])->send();

if ($response->isSuccessful()) {
    echo 'Card deleted successfully';
}
```

## Pay with Saved Card

```php
$response = $gateway->purchase([
    'amount' => '100.00',
    'currency' => 'TRY',
    'cardUserKey' => 'user_key_123',
    'cardToken' => 'card_token_abc',
    'description' => 'Order with saved card',
])->send();
```

---

*See also: [01-basic-payment.md](./01-basic-payment.md) for basic payment operations.*
