# p0-backlog-items - Work Plan

## TL;DR (For humans)

**What you'll get:** Three security/financial-risk fixes: (1) HMAC imza doğrulaması tüm API yanıtlarına eklenecek, (2) tanınmayan para biriminde sessiz TRY düşmesi yerine hata fırlatılacak, (3) iade sebebindeki yanlış string düzeltilecek. Tüm testler güncellenecek ve mevcut 243 test kırılmayacak.

**Why this approach:** Üç P0 maddesi birbirinden bağımsız, paralel yapılabilir. En kritiği signature validation — finansal güvenlik. mapCurrency ve RefundReason ise tek satırlık fix'ler.

**What it will NOT do:** P1/P2/P3 işlerine dokunmaz, webhook signature'ı değiştirmez, yeni Gateway metodu eklemez.

**Effort:** Short
**Risk:** Medium - Response signature validation requires careful field ordering per endpoint
**Decisions to sanity-check:** (1) Endpoint→field-order mapping'in doğruluğu, (2) mapCurrency hata mesajında desteklenen para birimleri listesi

Your next move: **Approve** this plan to start implementation, or ask for a Momus high-accuracy review first.

---

> TL;DR (machine): short, medium-risk, 3 P0 fixes: response HMAC validation, mapCurrency exception, RefundReason constant

## Scope
### Must have
- Response.php: normalizeTrailingZero() static helper + verifySignature(secretKey, orderedFields) method
- Response.php: isSignatureValid() accessor
- All request classes with real API calls: sendData() computes HMAC and stores _signature_valid
- Endpoint→field-order mapping for all 14+ table entries in the backlog
- AbstractRequest.php: mapCurrency() throws InvalidRequestException on unknown currency
- RefundRequest.php: use RefundReason::BUYER_REQUEST constant
- AbstractRequestTest.php: update currency test from "falls back to TL" to "throws exception"
- RefundRequestTest.php: update reason default assertion
- AcceptanceNotificationRequest is NOT in scope (already working)

### Must NOT have (guardrails, anti-slop, scope boundaries)
- No changes to AcceptNotificationRequest (already has isValid(), separate mechanism)
- No new Gateway methods
- No P1/P2/P3 work
- No deleting/creating files outside src/Message/ and tests/

## Verification strategy
> Zero human intervention - all verification is agent-executed.
- Test decision: tests-after (update existing + add new for signature validation)
- Framework: PHPUnit (12.0+ configured with phpunit.xml)
- Evidence: .omo/evidence/task-1-p0-backlog-items.md

## Execution strategy
### Parallel execution waves

Wave 1 (parallel — 3 independent items):
- Todo 1: Response signature validation (biggest scope)
- Todo 2: mapCurrency() fix + test
- Todo 3: RefundReason fix + test

### Dependency matrix
| Todo | Depends on | Blocks | Can parallelize with |
| --- | --- | --- | --- |
| 1 | none | none | 2, 3 |
| 2 | none | none | 1, 3 |
| 3 | none | none | 1, 2 |

## Todos
> Implementation + Test = ONE todo. Never separate.
<!-- APPEND TASK BATCHES BELOW THIS LINE WITH edit/apply_patch - never rewrite the headers above. -->
- [ ] 1. Response signature validation (sync API HMAC-SHA256)
  What to do / Must NOT do:
    - Add `public static function normalizeTrailingZero(string $value): string` to Response.php — removes trailing zeros from price-like strings. "50.00" → "50", "10.50" → "10.5", "10.510" → "10.51"
    - Add `public function verifySignature(string $secretKey, array $fieldNames): bool` to Response.php — computes HMAC-SHA256 of the concatenated field values (extracted from $this->data via $fieldNames, normalized with normalizeTrailingZero on price fields), compares with $this->data['signature'] via hash_equals().
    - Add `public function isSignatureValid(): ?bool` to Response.php — returns $this->data['_signature_valid'] ?? null.
    - Add a static field order map in Response.php with ALL 14+ endpoint entries from the backlog table. Keyed by endpoint identifier (e.g. '/payment/auth', '/payment/detail', etc.).
    - BUT: We can't know the endpoint from Response alone. Instead, add a method `setSignatureVerification(array $fieldNames, string $secretKey): void` that computes HMAC and stores `_signature_valid`.
    - Each Message class (PurchaseRequest, AuthorizeRequest, CaptureRequest, RefundRequest, VoidRequest, FetchTransactionRequest, CheckoutRequest, CheckoutStatusRequest, PayWithIyzicoInitializeRequest, PayWithIyzicoRetrieveRequest, BinNumberRequest, InstallmentRequest, CreateCardRequest, DeleteCardRequest, ListCardsRequest, CompletePurchaseRequest) that has a real API call: in sendData(), after getting the SDK result, determine field order -> extract values -> normalize -> compute HMAC -> compare with signature -> call $response->setSignatureVerification(...) or store _signature_valid in data.
    - Must NOT: change AcceptNotificationRequest's isValid() (separate mechanism, already correct).
    - Must NOT: change any Gateway.php methods.
    - Must NOT: break existing normalizeData() or any getter.
    - Tests: mock SDK result for at least one endpoint (e.g. Non-3DS purchase) with known values and expected signature. Test normalizeTrailingZero with edge cases. Test isSignatureValid() returns null when not set.
  Parallelization: Wave 1 | Blocked by: none | Blocks: none
  References:
    - omni pay-iyzico-backlog.md:23-74 (signature algorithm, field order table, trailing zero normalization)
    - src/Message/Response.php:24 (signature already in IYZICO_FIELDS)
    - src/Message/Response.php:30-61 (normalizeData reads from model)
    - src/Message/AcceptNotificationRequest.php:97-137 (existing isValid() pattern for reference)
    - vendor/iyzico/iyzipay-php/src/Iyzipay/Model/RefundReason.php:7-11 (constant pattern)
    - https://docs.iyzico.com/en/advanced/response-signature-validation.md
  Acceptance criteria (agent-executable):
    - `vendor/bin/phpunit --no-coverage tests/Message/ResponseTest.php` passes
    - Full suite: `vendor/bin/phpunit --no-coverage` — 243+ tests, 0 failures
    - `grep -r 'normalizeTrailingZero' src/` returns Response.php
    - `grep -r 'verifySignature' src/` returns Response.php
    - `grep -r '_signature_valid' src/Message/` returns all request files
  QA scenarios:
    - Happy: Response with valid signature, verify returns true
    - Failure: Tampered response (change price), verify returns false
    - Edge: Missing signature field, isSignatureValid() returns null
    - Edge: normalizeTrailingZero("50.00") = "50", normalizeTrailingZero("10.50") = "10.5", normalizeTrailingZero("abc") = "abc"
    - Edge: All tests pass
    Evidence: .omo/evidence/task-1-p0-backlog-items.md
  Commit: Y | feat(security): add HMAC-SHA256 signature validation for sync API responses

- [ ] 2. mapCurrency() throws InvalidRequestException on unknown currency
  What to do / Must NOT do:
    - AbstractRequest.php line 199: change `default => IyzicoCurrency::TL` to throw `new InvalidRequestException('Unsupported currency: [...] Supported: TRY, USD, EUR, GBP, RUB, IRR, NOK, CHF')`
    - Must NOT: change any other part of mapCurrency (the supported list is already correct per backlog)
    - AbstractRequestTest.php: change `'unknown falls back to TL'` test case to expect an InvalidRequestException instead
    - Must NOT: affect any other test
  Parallelization: Wave 1 | Blocked by: none | Blocks: none
  References:
    - src/Message/AbstractRequest.php:188-201 (mapCurrency)
    - src/Message/PurchaseRequest.php:8 (existing import of InvalidRequestException)
    - tests/Message/AbstractRequestTest.php:194 (test that needs updating)
    - omnipay-iyzico-backlog.md:76-106
  Acceptance criteria (agent-executable):
    - `vendor/bin/phpunit --filter testMapCurrency tests/Message/AbstractRequestTest.php` passes
    - Full suite: `vendor/bin/phpunit --no-coverage` — 0 failures
  QA scenarios:
    - Happy: mapCurrency('TRY') returns IyzicoCurrency::TL (no change)
    - Failure: mapCurrency('JPY') throws InvalidRequestException
    - Failure: mapCurrency('') throws InvalidRequestException
    Evidence: .omo/evidence/task-2-p0-backlog-items.md
  Commit: Y | fix: mapCurrency() throws InvalidRequestException instead of silent TRY fallback

- [ ] 3. RefundRequest uses RefundReason::BUYER_REQUEST constant
  What to do / Must NOT do:
    - RefundRequest.php: add `use Iyzipay\Model\RefundReason;` import
    - RefundRequest.php line 19: change `'reason' => $this->getParameter('reason') ?? 'buyer request'` to `'reason' => $this->getParameter('reason') ?? RefundReason::BUYER_REQUEST`
    - RefundRequestTest.php line 38: change `'buyer request'` to `RefundReason::BUYER_REQUEST` (value is `'buyer_request'`)
    - RefundRequestTest.php line 111-112: change `'buyer request'` to `RefundReason::BUYER_REQUEST`
    - Must NOT: change any other part of RefundRequest
  Parallelization: Wave 1 | Blocked by: none | Blocks: none
  References:
    - src/Message/RefundRequest.php:19
    - vendor/iyzico/iyzipay-php/src/Iyzipay/Model/RefundReason.php:8
    - tests/Message/RefundRequestTest.php:38,111
    - omnipay-iyzico-backlog.md:108-121
  Acceptance criteria (agent-executable):
    - `vendor/bin/phpunit --no-coverage tests/Message/RefundRequestTest.php` passes
    - `grep "RefundReason::BUYER_REQUEST" src/Message/RefundRequest.php` returns
    - Full suite: `vendor/bin/phpunit --no-coverage` — 0 failures
  QA scenarios:
    - Happy: Default reason is 'buyer_request' (with underscore)
    - Happy: Custom reason override works (e.g. setReason('fraud'))
    Evidence: .omo/evidence/task-3-p0-backlog-items.md
  Commit: Y | fix: RefundRequest uses RefundReason::BUYER_REQUEST SDK constant

## Final verification wave
> Runs in parallel after ALL todos. ALL must APPROVE. Surface results and wait for the user's explicit okay before declaring complete.
- [ ] F1. Plan compliance audit — all 3 P0 items implemented, scope boundaries respected
- [ ] F2. Code quality review — no logic errors, trailing zero correct, hash_equals() used, no silent failures
- [ ] F3. Real manual QA — full PHPUnit suite passes: `vendor/bin/phpunit --no-coverage`
- [ ] F4. Scope fidelity — no changes to AcceptNotificationRequest, Gateway.php, or P1+ files

## Commit strategy
- Todo 1: `feat(security): add HMAC-SHA256 signature validation for sync API responses`
- Todo 2: `fix: mapCurrency() throws InvalidRequestException instead of silent TRY fallback`
- Todo 3: `fix: RefundRequest uses RefundReason::BUYER_REQUEST SDK constant`
- All 3 commits can be squash-merged or kept separate

## Success criteria
- 10+ normalized test scenarios for signature validation
- mapCurrency() rejects unknown currencies with clear error
- RefundReason uses SDK constant (underscored)
- Full test suite: 0 failures
- No regressions on any existing functionality
