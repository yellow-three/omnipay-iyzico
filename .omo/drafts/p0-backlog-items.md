---
slug: p0-backlog-items
status: awaiting-approval
intent: clear
pending-action: write .omo/plans/p0-backlog-items.md
approach: 3 independent P0 fixes (signature validation, mapCurrency exception, RefundReason constant) + tests
---

# Draft: p0-backlog-items

## Components (topology ledger)
<!-- Lock the SHAPE before depth. One row per top-level component that can succeed or fail independently. -->
| id | outcome | status | evidence path |
|---|---|---|---|
| P0-1 | Response signature validation for sync API calls | active | src/Message/Response.php, src/Message/*Request.php |
| P0-2 | mapCurrency() throws InvalidRequestException on unknown currency | active | src/Message/AbstractRequest.php:188-201 |
| P0-3 | RefundRequest uses RefundReason::BUYER_REQUEST constant instead of literal 'buyer request' | active | src/Message/RefundRequest.php:19 |

## Open assumptions (announced defaults)
| assumption | adopted default | rationale | reversible? |
|---|---|---|---|
| mapCurrency() should throw on unknown currency | Throw InvalidRequestException with supported list | Silent TRY fallback = financial risk | Yes |
| Signature validation should verify in sendData() and expose via Response | Compute HMAC in sendData(), store `_signature_valid` in response data | Backlog requires it at the Message level, not just user-facing Response method | Yes |
| Refund reason default uses `RefundReason::BUYER_REQUEST` | Use SDK constant instead of literal string | SDK will track the actual value; current 'buyer request' (with space) is wrong | Yes |

## Findings (cited - path:lines)

1. **mapCurrency() silent fallback**: `src/Message/AbstractRequest.php:199` — `default => IyzicoCurrency::TL` silently returns TRY for unknown currencies (JPY, CAD, SEK, etc). Financial risk: incorrect amount charged.
2. **RefundRequest wrong reason literal**: `src/Message/RefundRequest.php:19` — `'buyer request'` (with space). SDK expects `'buyer_request'` (underscore). Correct value: `Iyzipay\Model\RefundReason::BUYER_REQUEST` = `"buyer_request"`. Confirmed at `vendor/iyzico/iyzipay-php/src/Iyzipay/Model/RefundReason.php:8`.
3. **Test for unknown currency fallback**: `tests/Message/AbstractRequestTest.php:194` — test expects `IyzicoCurrency::TL` for unknown currency. Must be updated to expect exception.
4. **Test for refund reason default**: `tests/Message/RefundRequestTest.php:38` and `:111` — test asserts `'buyer request'` literal. Must be updated to `RefundReason::BUYER_REQUEST`.
5. **RefundReason already imported**: `vendor/iyzico/iyzipay-php/src/Iyzipay/Model/RefundReason.php` — constants exist (`DOUBLE_PAYMENT`, `BUYER_REQUEST`, `FRAUD`, `OTHER`).
6. **InvalidRequestException already used**: `src/Message/PurchaseRequest.php:8` imports it from `Omnipay\Common\Exception\InvalidRequestException`.
7. **Existing test pattern**: `RefundRequestTest.php` and `AbstractRequestTest.php` use `expectException(InvalidRequestException::class)` for validation — same pattern for mapCurrency().
8. **Response already reads signature from models**: `src/Message/Response.php:24` — `'signature'` is in `IYZICO_FIELDS`. But no verification logic exists.
9. **Response->getData()**: Currently no way to check if signature is valid. Need `isSignatureValid(): ?bool`.
10. **Response model data extraction**: Response constructor already runs `normalizeData()` which reads getters from SDK model objects and returns arrays.

## Decisions (with rationale)

- **Decision: Response Signature Validation stored in sendData()**
  Rationale: Each Request class knows its endpoint and the correct field order. sendData() computes the HMAC-SHA256, compares with `$result->getSignature()`, and stores `_signature_valid` in the response data. Response exposes `isSignatureValid(): ?bool`. This keeps the logic where it belongs (Message layer) and follows the existing data flow.
  
- **Decision: No separate trait for signature helper**  
  Rationale: For 3 P0 items, the scope is contained. `normalizeTrailingZero()` can be a static method on Response. The 14+ field orders for different endpoints can be stored as constants or a static map on Response.

- **Decision: mapCurrency() throws InvalidRequestException**  
  Rationale: Already used in the codebase (PurchaseRequest.php imports it), matches Omnipay convention. Message includes supported currencies list.

- **Decision: RefundRequest uses SDK constant**  
  Rationale: Backlog specifically requires using `RefundReason::BUYER_REQUEST` instead of literal. This auto-adapts if SDK changes values.

## Scope IN

- P0-1: Trailing zero normalization helper + verifySignature in Response, endpoint field orders mapping, sendData() signature check in each Request class, isSignatureValid() in Response, tests
- P0-2: mapCurrency() throws InvalidRequestException on unknown currency, test updated
- P0-3: RefundRequest uses RefundReason::BUYER_REQUEST, tests updated

## Scope OUT (Must NOT have)

- NO webhook signature changes (already done in AcceptNotificationRequest)
- NO new Gateway methods or new API features
- NO changes to P1/P2/P3 backlog items
- NO refactoring beyond what's necessary for the P0 fix

## Open questions

None — all explored and resolved.

## Approval gate
status: awaiting-approval
<!-- When exploration is exhausted and unknowns are answered, set status: awaiting-approval. -->
<!-- That durable record is the loop guard: on a later turn read it and resume at the gate instead of re-running exploration. -->
