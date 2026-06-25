# Omnipay: Iyzico

**Iyzico gateway for Omnipay v3 payment processing library**

[![Latest Stable Version](https://poser.pugx.org/yellow-three/omnipay-iyzico/v/stable)](https://packagist.org/packages/yellow-three/omnipay-iyzico)
[![Total Downloads](https://poser.pugx.org/yellow-three/omnipay-iyzico/downloads)](https://packagist.org/packages/yellow-three/omnipay-iyzico)
[![CI](https://github.com/yellow-three/omnipay-iyzico/actions/workflows/ci.yml/badge.svg)](https://github.com/yellow-three/omnipay-iyzico/actions/workflows/ci.yml)
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

### Complete Purchase (3DS Callback)

After a 3D Secure payment is initiated, iyzico redirects the user back to your `returnUrl` with the result. Call `completePurchase()` with the callback data to complete the transaction:

```php
// In your callback route (iyzico sends POST data)
$response = $gateway->completePurchase($_POST)->send();

if ($response->isSuccessful()) {
    echo 'Payment successful! ID: ' . $response->getTransactionReference();
} else {
    echo 'Payment failed: ' . $response->getMessage();
}
```

> **Note:** iyzico sends the 3DS callback via **POST**, not GET. Your route handler must accept POST requests.

### Bin Number Lookup

Query credit card information by BIN (first 6 digits):

```php
$response = $gateway->fetchBinNumber([
    'binNumber' => '454359',
])->send();

$cardType = $response->getCardType();        // CREDIT_CARD, DEBIT_CARD etc
$cardAssociation = $response->getCardAssociation(); // VISA, MASTER_CARD etc
$cardFamily = $response->getCardFamily();
$bankName = $response->getBankName();
$bankCode = $response->getBankCode();
```

### Installment Information

Query installment options for a given BIN:

```php
$response = $gateway->fetchInstallment([
    'binNumber' => '454359',
])->send();

// $response contains installment details including installment prices
```

### Card Storage

Save, list, and delete user cards for future purchases:

```php
// Save a card
$response = $gateway->createCard([
    'card' => $cardData,
    'email' => 'user@example.com',
    'cardUserKey' => 'user_key_123',
])->send();

// List saved cards
$response = $gateway->listCards([
    'cardUserKey' => 'user_key_123',
])->send();

// Delete a saved card
$response = $gateway->deleteCard([
    'cardToken' => 'card_token_123',
    'cardUserKey' => 'user_key_123',
])->send();
```

### Pay with iyzico (PWI)

```php
// Initialize PWI payment
$response = $gateway->payWithIyzico([
    'amount' => '100.00',
    'currency' => 'TRY',
    'basketId' => 'order_123',
    'card' => $cardData,
    'buyer' => $buyerData,
    'shippingAddress' => $shippingData,
    'billingAddress' => $billingData,
    'basketItems' => $basketItems,
])->send();

if ($response->isRedirect()) {
    // Redirect user to iyzico PWI page
    $response->redirect();
}

// Retrieve PWI payment status
$response = $gateway->payWithIyzicoStatus([
    'token' => 'pwi_token_from_callback',
])->send();
```

### Webhook / Accept Notification

Handle iyzico payment callbacks and webhook notifications with HMAC-SHA256 signature verification:

```php
// In your webhook route (iyzico sends POST with JSON payload)
$response = $gateway->acceptNotification($_POST)->send();

$transactionRef = $response->getTransactionReference();
$status = $response->getTransactionStatus();
$message = $response->getMessage();

// Verify the webhook signature (recommended for production)
if ($response->isValid()) {
    // Signature verified — process the notification
    switch ($status) {
        case NotificationInterface::STATUS_COMPLETED:
            // Payment successful
            break;
        case NotificationInterface::STATUS_PENDING:
            // Payment pending (INIT_THREEDS, BKM_POS_SELECTED, etc.)
            break;
        case NotificationInterface::STATUS_FAILED:
            // Payment failed
            break;
    }
} else {
    // Invalid signature or empty data — discard
    http_response_code(400);
    exit;
}
```

#### Webhook Formats

iyzico sends webhooks in two formats:

| Format | Identification | Key Fields |
|--------|--------------|------------|
| **Direct** | No `token` parameter | `paymentId`, `iyziPaymentId`, `iyziEventType`, `status` |
| **HPP** (Hosted Payment Page) | Has `token` parameter | `token`, `iyziPaymentId`, `iyziEventType`, `status` |

The gateway auto-detects the format based on the presence of `token` in the payload.

#### Signature Verification

The HMAC-SHA256 signature is computed differently for each format:

- **Direct:** `hash_hmac('sha256', $secretKey . $iyziEventType . $paymentId . $paymentConversationId . $status, $secretKey)`
- **HPP:** `hash_hmac('sha256', $secretKey . $iyziEventType . $iyziPaymentId . $token . $paymentConversationId . $status, $secretKey)`

Call `$response->isValid()` to verify the signature automatically.

#### Transaction Reference Fallback

`getTransactionReference()` returns the first available value from:
1. `$data['paymentId']` (Direct format)
2. `$data['iyziPaymentId']` (HPP format)
3. `$data['token']` (HPP format)
4. `$this->getParameter('paymentId')` (set via Gateway)
5. `$this->getParameter('token')` (set via Gateway)

#### Status Mapping

| Webhook Status | NotificationInterface Constant |
|---------------|------------------------------|
| `SUCCESS` | `STATUS_COMPLETED` |
| `FAILURE` | `STATUS_FAILED` |
| `INIT_THREEDS`, `CALLBACK_THREEDS`, `BKM_POS_SELECTED`, `INIT_APM`, `INIT_CONTACTLESS`, `INIT_BANK_TRANSFER`, `INIT_CREDIT`, `PENDING_CREDIT` | `STATUS_PENDING` |

### PreAuth Checkout Form

Initialize a pre-authorization via the iyzico checkout form:

```php
$response = $gateway->checkoutFormPreAuth([
    'amount' => '100.00',
    'currency' => 'TRY',
    'basketId' => 'order_123',
    'returnUrl' => 'https://yoursite.com/payment/callback',
    'enabledInstallments' => [2, 3, 6, 9],
    'card' => $cardData,
])->send();

if ($response->isRedirect()) {
    $response->redirect();
}
```

### Pay with iyzico PreAuth

Pre-authorization via the Pay with iyzico page:

```php
$response = $gateway->payWithIyzicoPreAuth([
    'amount' => '100.00',
    'currency' => 'TRY',
    'basketId' => 'order_123',
    'returnUrl' => 'https://yoursite.com/payment/callback',
    'card' => $cardData,
    'buyer' => $buyerData,
    'shippingAddress' => $shippingData,
    'billingAddress' => $billingData,
    'basketItems' => $basketItems,
])->send();

if ($response->isRedirect()) {
    $response->redirect();
}
```

### Basic 3D Secure PreAuth

Direct card pre-authorization with 3DS:

```php
$response = $gateway->basicThreedsPreAuth([
    'amount' => '100.00',
    'currency' => 'TRY',
    'returnUrl' => 'https://yoursite.com/payment/callback',
    'card' => [
        'number' => '4543590000000006',
        'expiryMonth' => '12',
        'expiryYear' => '2030',
        'cvv' => '123',
    ],
])->send();

if ($response->isRedirect()) {
    echo $response->getHtmlContent();
}
```

### Refund to Balance

Refund a payment to the buyer's iyzico balance:

```php
$response = $gateway->refundToBalance([
    'paymentId' => 'pay_abc123',
    'returnUrl' => 'https://yoursite.com/callback',
])->send();

if ($response->isSuccessful()) {
    echo 'Refund successful';
}
```

### Settlement to Balance

Settle (transfer) funds from a sub-merchant to the main merchant balance:

```php
$response = $gateway->settlementToBalance([
    'subMerchantKey' => 'smk_001',
    'amount' => '50.00',
    'returnUrl' => 'https://yoursite.com/callback',
])->send();
```

### Plus Installment Payment

Make a payment with plus installment support:

```php
$response = $gateway->purchasePlusInstallment([
    'amount' => '100.00',
    'currency' => 'TRY',
    'installment' => 3,
    'connectorName' => 'akbank',
    'plusInstallmentUsage' => 1,
    'card' => $cardData,
    'buyer' => $buyerData,
    'shippingAddress' => $shippingData,
    'billingAddress' => $billingData,
    'basketItems' => $basketItems,
])->send();
```

### Fetch Loyalty

Query loyalty points for a card:

```php
$response = $gateway->fetchLoyalty([
    'currency' => 'TRY',
    'card' => [
        'number' => '4543590000000006',
        'expiryMonth' => '12',
        'expiryYear' => '2030',
    ],
])->send();

$balance = $response->getData()['balance'];
```

### iyzico Link

Create and manage payment links:

```php
// Save a product
$response = $gateway->iyziLinkSaveProduct([
    'name' => 'Product A',
    'description' => 'Product description',
    'price' => 100.00,
    'currencyCode' => 'TRY',
    'addressIgnorable' => true,
    'installmentRequested' => false,
    'sourceType' => 'WEB',
    'stockEnabled' => true,
    'stockCount' => 100,
])->send();

$referenceCode = $response->getData()['productReferenceCode'];

// Retrieve a product
$response = $gateway->iyziLinkRetrieveProduct([
    'productReferenceCode' => $referenceCode,
])->send();

// List all products
$response = $gateway->iyziLinkRetrieveAllProduct()->send();

// Delete a product
$response = $gateway->iyziLinkDeleteProduct([
    'productReferenceCode' => $referenceCode,
])->send();

// Update product status (ACTIVE / PASSIVE)
$response = $gateway->iyziLinkUpdateProductStatus([
    'productReferenceCode' => $referenceCode,
    'status' => 'ACTIVE',
])->send();

// Create a fast link (single-use payment link)
$response = $gateway->iyziLinkCreateFastLink([
    'description' => 'Fast payment',
    'price' => 50.00,
    'currencyCode' => 'TRY',
    'sourceType' => 'WEB',
])->send();

$payUrl = $response->getData()['payUrl'];

// Search merchant products
$response = $gateway->iyziLinkSearchMerchantProducts([
    'page' => 1,
    'count' => 10,
])->send();
```

### Reporting

Query payment reports:

```php
// Payment detail by paymentId
$response = $gateway->reportingPaymentDetail([
    'paymentId' => 'pay_abc123',
])->send();

// Payment transactions by date (YYYY-MM-DD)
$response = $gateway->reportingPaymentTransaction([
    'transactionDate' => '2024-01-15',
    'page' => 0,
])->send();

// Scroll transaction (paginated)
$response = $gateway->reportingScrollTransaction([
    'transactionDate' => '2024-01-15',
    'page' => 0,
])->send();
```

### BKM Express

Initialize and retrieve BKM Express payments:

```php
// Initialize BKM (full — with buyer/card data)
$response = $gateway->bkmInitialize([
    'amount' => '100.00',
    'currency' => 'TRY',
    'basketId' => 'order_123',
    'returnUrl' => 'https://yoursite.com/callback',
    'enabledInstallments' => [2, 3, 6, 9],
    'card' => $cardData,
])->send();

if ($response->isRedirect()) {
    echo $response->getHtmlContent();
}

// Initialize BKM (basic — no card data required)
$response = $gateway->basicBkmInitialize([
    'amount' => '100.00',
    'currency' => 'TRY',
    'basketId' => 'order_123',
    'returnUrl' => 'https://yoursite.com/callback',
])->send();

// Retrieve BKM status
$response = $gateway->bkmStatus([
    'paymentId' => 'pay_abc123',
    'conversationId' => 'conv_123',
])->send();
```

### APM (Alternative Payment Methods)

Initialize payments via alternative methods (SOFORT, IDEAL, QIWI, GIROPAY):

```php
// Initialize APM payment
$response = $gateway->apmInitialize([
    'amount' => '100.00',
    'currency' => 'EUR',
    'basketId' => 'order_123',
    'apmType' => 'SOFORT',
    'merchantOrderId' => 'order_456',
    'countryCode' => 'DE',
    'returnUrl' => 'https://yoursite.com/callback',
    'card' => $cardData,
    'buyer' => $buyerData,
    'shippingAddress' => $shippingData,
    'billingAddress' => $billingData,
    'basketItems' => $basketItems,
])->send();

if ($response->isRedirect()) {
    $response->redirect();
}

// Retrieve APM payment status
$response = $gateway->apmRetrieve([
    'paymentId' => 'pay_abc123',
    'conversationId' => 'conv_123',
])->send();
```

### Marketplace (Sub-Merchant Operations)

Manage sub-merchant accounts and payments in a marketplace:

```php
// Create a sub-merchant
$response = $gateway->createSubMerchant([
    'subMerchantExternalId' => 'ext_001',
    'subMerchantType' => 'PERSONAL',
    'price' => '1.0',
    'currency' => 'TRY',
    'name' => 'Sub Merchant Name',
    'email' => 'sub@example.com',
    'gsmNumber' => '+905551112233',
    'address' => '123 Street',
    'iban' => 'TR123456789012345678901234',
    'contactName' => 'John',
    'contactSurname' => 'Doe',
    'identityNumber' => '11111111111',
    'taxNumber' => '1234567890',
])->send();

$subMerchantKey = $response->getData()['subMerchantKey'];

// Update a sub-merchant
$response = $gateway->updateSubMerchant([
    'subMerchantKey' => $subMerchantKey,
    'email' => 'newemail@example.com',
    'name' => 'Updated Name',
    'iban' => 'TR987654321098765432109876',
    'contactName' => 'Jane',
    'contactSurname' => 'Doe',
    'identityNumber' => '11111111111',
    'taxNumber' => '1234567890',
    'currency' => 'TRY',
])->send();

// Retrieve a sub-merchant
$response = $gateway->retrieveSubMerchant([
    'subMerchantExternalId' => 'ext_001',
])->send();

// Approve a sub-merchant payment
$response = $gateway->approvePayment([
    'paymentTransactionId' => 'tx_abc123',
])->send();

// Disapprove a sub-merchant payment
$response = $gateway->disapprovePayment([
    'paymentTransactionId' => 'tx_abc123',
])->send();

// Cross-booking: move money FROM sub-merchant to main merchant
$response = $gateway->crossBookingFrom([
    'subMerchantKey' => $subMerchantKey,
    'price' => '10.00',
    'reason' => 'Commission fee',
])->send();

// Cross-booking: move money TO sub-merchant from main merchant
$response = $gateway->crossBookingTo([
    'subMerchantKey' => $subMerchantKey,
    'price' => '10.00',
    'reason' => 'Bonus payment',
])->send();

// Update sub-merchant payment item
$response = $gateway->updateSubMerchantPaymentItem([
    'paymentTransactionId' => 'tx_abc123',
    'subMerchantKey' => $subMerchantKey,
    'subMerchantPrice' => '5.00',
])->send();
```

### Subscription Management

Full lifecycle for recurring subscription payments — products, pricing plans, customers, and subscriptions.

#### Product & Pricing Plan Setup

```php
// Create a subscription product
$response = $gateway->createSubscriptionProduct([
    'name' => 'Premium Plan',
    'description' => 'Monthly premium access',
])->send();

$productRefCode = $response->getData()['productReferenceCode'];

// Create a pricing plan for the product
$response = $gateway->createSubscriptionPricingPlan([
    'name' => 'Monthly Premium',
    'productReferenceCode' => $productRefCode,
    'price' => '49.99',
    'currencyCode' => 'TRY',
    'paymentInterval' => 'MONTHLY',
    'paymentIntervalCount' => 1,
    'trialPeriodDays' => 7,
    'recurrenceCount' => 12,
])->send();

$planRefCode = $response->getData()['pricingPlanReferenceCode'];

// List / retrieve / update / delete products and plans
$response = $gateway->listSubscriptionProducts()->send();
$response = $gateway->retrieveSubscriptionProduct([
    'productReferenceCode' => $productRefCode,
])->send();
$response = $gateway->deleteSubscriptionProduct([
    'productReferenceCode' => $productRefCode,
])->send();

$response = $gateway->listSubscriptionPricingPlans()->send();
$response = $gateway->retrieveSubscriptionPricingPlan([
    'pricingPlanReferenceCode' => $planRefCode,
])->send();
$response = $gateway->deleteSubscriptionPricingPlan([
    'pricingPlanReferenceCode' => $planRefCode,
])->send();
```

#### Customer Management

```php
// Create a customer
$response = $gateway->createSubscriptionCustomer([
    'email' => 'user@example.com',
    'gsmNumber' => '+905551112233',
    'name' => 'John',
    'surname' => 'Doe',
    'identityNumber' => '11111111111',
    'billingAddress' => '123 Street',
    'billingCity' => 'Istanbul',
    'billingCountry' => 'Turkey',
    'billingZipCode' => '34000',
    'shippingAddress' => '123 Street',
    'shippingCity' => 'Istanbul',
    'shippingCountry' => 'Turkey',
    'shippingZipCode' => '34000',
])->send();

$customerRefCode = $response->getData()['customerReferenceCode'];

// List / retrieve / update / delete customers
$response = $gateway->listSubscriptionCustomers()->send();
$response = $gateway->retrieveSubscriptionCustomer([
    'customerReferenceCode' => $customerRefCode,
])->send();
$response = $gateway->deleteSubscriptionCustomer([
    'customerReferenceCode' => $customerRefCode,
])->send();
```

#### Create Subscription (with card on file)

```php
$response = $gateway->createSubscription([
    'pricingPlanReferenceCode' => $planRefCode,
    'subscriptionInitialStatus' => 'ACTIVE',
    'cardNumber' => '4543590000000006',
    'cardHolderName' => 'John Doe',
    'expireMonth' => '12',
    'expireYear' => '2030',
    'cvc' => '123',
    'cardName' => 'My Card',
    'customerEmail' => 'user@example.com',
    'customerGsmNumber' => '+905551112233',
    'customerName' => 'John',
    'customerSurname' => 'Doe',
    'customerIdentityNumber' => '11111111111',
    'customerBillingAddress' => '123 Street',
    'customerBillingCity' => 'Istanbul',
    'customerBillingCountry' => 'Turkey',
    'customerBillingZipCode' => '34000',
    'customerShippingAddress' => '123 Street',
    'customerShippingCity' => 'Istanbul',
    'customerShippingCountry' => 'Turkey',
    'customerShippingZipCode' => '34000',
])->send();

$subscriptionRefCode = $response->getData()['subscriptionReferenceCode'];
```

#### Create Subscription (with existing customer)

```php
$response = $gateway->createSubscriptionWithCustomer([
    'pricingPlanReferenceCode' => $planRefCode,
    'customerReferenceCode' => $customerRefCode,
    'subscriptionInitialStatus' => 'ACTIVE',
])->send();
```

#### Subscription Checkout Form

Let the customer enter their own card via iyzico's hosted form:

```php
$response = $gateway->createSubscriptionCheckoutForm([
    'pricingPlanReferenceCode' => $planRefCode,
    'callbackUrl' => 'https://yoursite.com/subscription/callback',
    'subscriptionInitialStatus' => 'ACTIVE',
    'customerEmail' => 'user@example.com',
    'customerName' => 'John',
    'customerSurname' => 'Doe',
    'customerIdentityNumber' => '11111111111',
    'customerBillingAddress' => '123 Street',
    'customerBillingCity' => 'Istanbul',
    'customerBillingCountry' => 'Turkey',
    'customerBillingZipCode' => '34000',
    'customerShippingAddress' => '123 Street',
    'customerShippingCity' => 'Istanbul',
    'customerShippingCountry' => 'Turkey',
    'customerShippingZipCode' => '34000',
])->send();

if ($response->isRedirect()) {
    $response->redirect();
}

// After callback
$response = $gateway->retrieveSubscriptionCheckoutForm([
    'token' => 'token_from_callback',
])->send();
```

#### Subscription Lifecycle

```php
// Retrieve subscription details
$response = $gateway->retrieveSubscriptionDetails([
    'subscriptionReferenceCode' => $subscriptionRefCode,
])->send();

// Activate (after initial PENDING status)
$response = $gateway->activateSubscription([
    'subscriptionReferenceCode' => $subscriptionRefCode,
])->send();

// Cancel
$response = $gateway->cancelSubscription([
    'subscriptionReferenceCode' => $subscriptionRefCode,
])->send();

// Retry a failed payment
$response = $gateway->retrySubscription([
    'subscriptionReferenceCode' => $subscriptionRefCode,
])->send();

// Upgrade to a new pricing plan
$response = $gateway->upgradeSubscription([
    'subscriptionReferenceCode' => $subscriptionRefCode,
    'newPricingPlanReferenceCode' => $newPlanRefCode,
    'upgradePeriod' => 'NEXT_PERIOD',
    'useTrial' => false,
    'resetRecurrenceCount' => true,
])->send();

// List subscriptions (paginated, filterable)
$response = $gateway->listSubscriptions([
    'page' => 0,
    'count' => 10,
    'customerReferenceCode' => $customerRefCode,
    'subscriptionStatus' => 'ACTIVE',
])->send();

// Search subscriptions (paginated, filterable by date range)
$response = $gateway->searchSubscriptions([
    'page' => 0,
    'count' => 10,
    'pricingPlanReferenceCode' => $planRefCode,
    'startDate' => '2024-01-01',
    'endDate' => '2024-12-31',
])->send();

// Update card for all subscriptions of a customer (checkout form)
$response = $gateway->updateSubscriptionCard([
    'customerReferenceCode' => $customerRefCode,
    'callbackUrl' => 'https://yoursite.com/card-update/callback',
])->send();

// Update card for a specific subscription (direct)
$response = $gateway->updateSubscriptionCardForSubscription([
    'subscriptionReferenceCode' => $subscriptionRefCode,
    'cardNumber' => '5528790000000008',
    'cardHolderName' => 'John Doe',
    'expireMonth' => '06',
    'expireYear' => '2031',
    'cvc' => '456',
])->send();
```

## End-to-End 3DS Flow

A complete 3D Secure payment flow from start to finish:

### Step 1: Initialize the Gateway

```php
$gateway = Omnipay::create('Iyzico');
$gateway->setApiKey('your-api-key');
$gateway->setSecretKey('your-secret-key');
$gateway->setTestMode(true); // Sandbox mode
```

### Step 2: Initiate Purchase with 3DS

```php
$response = $gateway->purchase([
    'amount' => '150.00',
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
    // Render the 3DS form in browser
    echo $response->getHtmlContent();
    exit;
}
```

### Step 3: Handle 3DS Callback

iyzico redirects the user back to your `returnUrl` with POST data:

```php
// Route: POST /payment/callback
$response = $gateway->completePurchase($_POST)->send();

if ($response->isSuccessful()) {
    $paymentId = $response->getTransactionReference();
    echo 'Payment successful! ID: ' . $paymentId;
} else {
    echo 'Payment failed: ' . $response->getMessage();
}
```

### Step 4: Verify with Webhook (Optional)

iyzico also sends a webhook after the payment is finalized:

```php
// Route: POST /payment/webhook
$response = $gateway->acceptNotification(
    json_decode(file_get_contents('php://input'), true) ?? []
)->send();

if ($response->isValid()) {
    // Process based on status
    $transactionRef = $response->getTransactionReference();
    $status = $response->getTransactionStatus();
    // Update your order status
}
```

> **Important:** The webhook `$_POST` data must be decoded from JSON (iyzico sends JSON payloads to webhooks). For 3DS callbacks, iyzico sends form-encoded POST data — use `$_POST` directly.

## Response Methods

The `Response` object returned by `->send()` provides these methods:

| Method | Return Type | Description |
|--------|-------------|-------------|
| `isSuccessful()` | `bool` | Payment successful (status = "success") |
| `isPending()` | `bool` | Payment pending (status = "pending") |
| `isRedirect()` | `bool` | Whether response requires redirect |
| `getTransactionReference()` | `?string` | Payment ID or conversation ID |
| `getTransactionId()` | `?string` | Payment transaction ID |
| `getPaymentId()` | `?string` | iyzico payment ID |
| `getPaymentStatus()` | `?string` | Payment status from iyzico |
| `getStatus()` | `?string` | Raw status field |
| `getConversationId()` | `?string` | Conversation ID |
| `getToken()` | `?string` | Checkout/PWI token |
| `getMessage()` | `?string` | Error message or status |
| `getCode()` | `?string` | Error code |
| `getPaidPrice()` | `?string` | Paid price amount |
| `getCheckoutFormContent()` | `?string` | Checkout form iframe content |
| `getCardType()` | `?string` | CREDIT_CARD, DEBIT_CARD |
| `getCardAssociation()` | `?string` | VISA, MASTER_CARD |
| `getCardFamily()` | `?string` | Card family/brand name |
| `getCardToken()` | `?string` | Saved card token |
| `getCardUserKey()` | `?string` | Card user key |
| `getBinNumber()` | `?string` | BIN (first 6 digits) |
| `getLastFourDigits()` | `?string` | Last 4 digits of card |
| `getAuthCode()` | `?string` | Authorization code |
| `getConnectorName()` | `?string` | Connector/bank name |
| `getPaymentTransactionId()` | `?string` | Payment transaction ID |
| `getBankName()` | `?string` | Issuing bank name |
| `getBankCode()` | `?string` | Issuing bank code |
| `getCommercial()` | `?int` | Commercial card flag (0/1) |
| `getInstallmentDetails()` | `?array` | Installment option details |
| `getExternalId()` | `?string` | External reference ID |
| `getCardAlias()` | `?string` | Saved card alias |
| `getCardBankCode()` | `?string` | Card bank code |
| `getCardBankName()` | `?string` | Card bank name |
| `getSignature()` | `?string` | Webhook HMAC signature |
| `getMdStatus()` | `?string` | 3DS status code |
| `getCallbackUrl()` | `?string` | Callback URL |
| `getHtmlContent()` | `?string` | 3DS/Checkout HTML content |
| `getPaymentPageUrl()` | `?string` | Payment page URL |
| `getPayWithIyzicoPageUrl()` | `?string` | PWI page URL |
| `getPayWithIyzicoContent()` | `?string` | PWI iframe content |

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
| `installment` | int | `1` | Installment count (1 = peşin/tek çekim. 0 geçersizdir!) |
| `identityNumber` | string | `''` | Buyer TCKN |
| `paymentChannel` | string | `WEB` | WEB, MOBILE, MOBILE_WEB |
| `paymentGroup` | string | `PRODUCT` | PRODUCT, LISTING, SUBSCRIPTION |
| `cardUserKey` | string | `''` | Card storage user key |
| `cardToken` | string | `''` | Saved card token |
| `binNumber` | string | `''` | BIN (first 6 digits) for lookup |

## Sandbox Testing

1. Register at [sandbox-merchant.iyzipay.com](https://sandbox-merchant.iyzipay.com/auth)
2. Login with SMS code `123456`
3. Get API keys from Settings > API Keys
4. Use test cards from [docs.iyzico.com/ek-bilgiler/test-kartlari](https://docs.iyzico.com/ek-bilgiler/test-kartlari)
5. 3DS password: `283126`

### Test Cards (Sandbox)

**Successful:**

| Card Number | Bank | Brand | Type |
|---|---|---|---|
| 5890040000000016 | Akbank | Master Card | Debit |
| 5526080000000006 | Akbank | Master Card | Credit |
| 9792072000017956 | Akbank | Troy | Credit |
| 4766620000000001 | Denizbank | Visa | Debit |
| 4603450000000000 | Denizbank | Visa | Credit |
| 5311570000000005 | QNB | Master Card | Credit |
| 9792030000000000 | QNB | Troy | Credit |
| 5400360000000003 | Garanti | Master Card | Credit |
| 5528790000000008 | Halkbank | Master Card | Credit |
| 4543590000000006 | İş Bankası | Visa | Credit |
| 4157920000000002 | Vakıfbank | Visa | Credit |
| 5451030000000000 | Yapı Kredi | Master Card | Credit |

**Error (simulate failures):**

| Card Number | Description |
|---|---|
| 4111111111111129 | Not sufficient funds |
| 4129111111111111 | Do not honour |
| 4128111111111112 | Invalid transaction |
| 4125111111111115 | Expired card |
| 4124111111111116 | Invalid cvc2 |
| 4121111111111119 | Fraud suspect |

All test cards: expiry `12/2030`, CVV `123` (or any random value in correct format).

## Important Notes

### Installment

`installment` must be >= 1. Value `0` causes iyzico error 5012 ("Taksit seçeneği geçersizdir"). Use `1` for single payment (peşin).

### Required Buyer Fields

iyzico requires the following buyer fields. Missing any causes validation errors:

- `email` — error 3: "email gönderilmesi zorunludur"
- `billingAddress1` (maps to `registrationAddress`) — error 5026
- `shippingCity` — error 5038: "Shipping address city gönderilmesi zorunludur"

### 3D Secure Callback

- iyzico 3DS callback sends **POST** (not GET) — your route must support both methods
- iyzico 3DS callback has **no CSRF token** — exempt the callback route from CSRF verification
- `conversationData` may be `null` in the callback even on success — check `mdStatus=1` + `status=success` as fallback
- When `conversationData` is null, skip `completePurchase()` and redirect directly to transaction status page

### iyzico API Quirks

- `Currency::TRY` is the correct constant (not `Currency::TL`)
- `PaymentChannel::WEB_POS` does not exist — use `WEB`
- `PaymentGroup::INHERITED` does not exist — use `PRODUCT`, `LISTING`, or `SUBSCRIPTION`
- 3DS Initialize returns HTML content (`getHtmlContent()`), not a redirect URL — render it directly in the browser

### Webhook / AcceptNotification

- iyzico webhook payload'ları JSON formatında gelir — `$_POST` yerine `json_decode(file_get_contents('php://input'), true)` kullanarak çözün
- Webhook payload'ında `mdStatus`, `errorMessage`, `errorCode` alanları yoktur — bunlar sadece senkron 3DS callback'lerinde bulunur
- HMAC-SHA256 imzası `signature` alanında gelir; `isValid()` ile doğrulayın
- İmza doğrulaması için Gateway'e `setSecretKey()` ile secret key tanımlanmış olmalıdır
- `getTransactionReference()` fallback zinciri: paymentId → iyziPaymentId → token → ParameterBag
- SUCCESS/FAILURE dışındaki status'ler (INIT_THREEDS, CALLBACK_THREEDS, BKM_POS_SELECTED) `STATUS_PENDING` olarak map'lenir — bu, yanlışlıkla refund/void gönderilmesini engeller

## Requirements

- PHP >= 8.1
- Omnipay Common v3
- iyzico/iyzipay-php v2

## License

MIT License. See [LICENSE](LICENSE) for details.
