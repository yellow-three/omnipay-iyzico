# Webhook / AcceptNotification

Handle iyzico webhook notifications with HMAC-SHA256 signature verification.

## Basic Webhook Handler

```php
// Route: POST /payment/webhook
// iyzico sends JSON payload

$payload = json_decode(file_get_contents('php://input'), true);

$response = $gateway->acceptNotification($payload)->send();

// Verify HMAC-SHA256 signature
if (!$response->isValid()) {
    http_response_code(400);
    echo 'Invalid signature';
    exit;
}

$transactionRef = $response->getTransactionReference();
$status = $response->getTransactionStatus();
$message = $response->getMessage();

switch ($status) {
    case NotificationInterface::STATUS_COMPLETED:
        // Payment successful — fulfill order
        break;
    case NotificationInterface::STATUS_PENDING:
        // Payment in progress (3DS, BKM, etc.)
        break;
    case NotificationInterface::STATUS_FAILED:
        // Payment failed — notify user
        break;
}

http_response_code(200);
echo 'OK';
```

## Webhook Formats

### Direct Format

Used for standard API payments:

```json
{
    "paymentId": "pay_123",
    "iyziPaymentId": "iyzi_123",
    "iyziEventType": "DIRECT",
    "paymentConversationId": "conv_123",
    "status": "SUCCESS",
    "signature": "abc123..."
}
```

### HPP Format (Hosted Payment Page)

Used for checkout form / PWI payments:

```json
{
    "token": "tok_456",
    "iyziPaymentId": "iyzi_789",
    "iyziEventType": "HPP_EVENT",
    "paymentConversationId": "conv_789",
    "status": "SUCCESS",
    "signature": "def456..."
}
```

## Signature Verification Details

The `isValid()` method computes the expected HMAC-SHA256 signature and compares it using `hash_equals()` (timing-attack safe).

| Format | Message Format |
|--------|---------------|
| Direct | `$secretKey . $iyziEventType . $paymentId . $paymentConversationId . $status` |
| HPP | `$secretKey . $iyziEventType . $iyziPaymentId . $token . $paymentConversationId . $status` |

## Important Notes

- Webhook payloads are **JSON** (not form-encoded) — use `json_decode(file_get_contents('php://input'), true)`
- `$_POST` will be empty for JSON payloads — do NOT pass `$_POST` directly
- Webhook payloads do NOT contain `mdStatus`, `errorMessage`, or `errorCode`
- The `signature` field is required for `isValid()` to work
- `getSecretKey()` must be set on the Gateway before calling `acceptNotification()`
- Non-terminal statuses (INIT_THREEDS, BKM_POS_SELECTED, etc.) map to `STATUS_PENDING`
- Always call `isValid()` before processing the webhook in production

---

*See also: [02-3ds-flow.md](./02-3ds-flow.md) for 3DS callback handling.*
