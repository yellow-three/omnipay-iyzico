# BIN Lookup & Installment Information

Query credit card details and installment options by BIN (first 6 digits).

## BIN Number Lookup

Get card type, association, family, and issuing bank:

```php
$response = $gateway->fetchBinNumber([
    'binNumber' => '454359',
])->send();

if ($response->isSuccessful()) {
    echo 'Card Type: ' . $response->getCardType();          // CREDIT_CARD, DEBIT_CARD
    echo 'Card Association: ' . $response->getCardAssociation(); // VISA, MASTER_CARD, TROY
    echo 'Card Family: ' . $response->getCardFamily();       // Maximum, World, etc.
    echo 'Bank Name: ' . $response->getBankName();           // İş Bankası
    echo 'Bank Code: ' . $response->getBankCode();            // 64
    echo 'Commercial: ' . $response->getCommercial();         // 0 or 1
}
```

## Installment Information

Query installment options for a given BIN:

```php
$response = $gateway->fetchInstallment([
    'binNumber' => '454359',
])->send();

if ($response->isSuccessful()) {
    $details = $response->getInstallmentDetails();
    foreach ($details as $installment) {
        echo 'Bank: ' . $installment['bankName'] . PHP_EOL;
        foreach ($installment['installmentPrices'] as $price) {
            echo '  ' . $price['installmentNumber'] . ' taksit: ' . $price['totalPrice'] . PHP_EOL;
        }
    }
}
```

## Use with Checkout

You can pass `enabledInstallments` to limit options:

```php
$response = $gateway->checkout([
    'amount' => '100.00',
    'currency' => 'TRY',
    'basketId' => 'order_123',
    'returnUrl' => 'https://yoursite.com/callback',
    'enabledInstallments' => [2, 3, 6], // Only 2, 3, or 6 installments
    'card' => $cardData,
])->send();
```

---

*See also: [01-basic-payment.md](./01-basic-payment.md) for basic payment operations.*
