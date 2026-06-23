# comprehensive-feature-completion - Work Plan

## TL;DR (For humans)

**What you'll get:** A production-ready iyzico Omnipay gateway with:
- All pending bug fixes committed (installment default, redirect logic, docs)
- Full unit test coverage for every request/response class
- Three major new features: BIN/installment lookup, card storage (save/delete/list), and PayWithIyzico
- CI via GitHub Actions so tests run automatically on every push

**Why this approach:** Tests come before new features because untested code breaks silently. Card storage and BIN queries are the #1 most-requested features after basic payments. PWI (PayWithIyzico) is iyzico's flagship alternative payment method.

**What it will NOT do:** Implement subscriptions, marketplace/submerchant, IyzicoLink, reporting, physical POS, BKM Express, or APM. These are documented as future scope.

**Effort:** Large
**Risk:** Medium — core payment flows already work; the risk is in mocking the iyzico SDK for test isolation and in PWI's complex redirect flow

**Decisions I made for you (veto any):**
- Feature priority: Installment/BIN > Card Storage > PWI > everything else deferred
- Test approach: tests-after for existing code, TDD for new features
- Mock-heavy testing: no real API keys — SDK model classes are mocked
- PHP 8.1 minimum (matching existing composer.json)
- No end-to-end tests against sandbox (requires real credentials)

Your next move: Approve this plan (reply "approve") or request changes. After approval, execution begins and the worker will implement each todo in dependency order.

---

> TL;DR (machine): Effort: Large | Risk: Medium | Deliverables: committed fixes, 100% test coverage for 12 message classes + Gateway, InstallmentInfo/BIN/CardStorage/PWI features, GitHub Actions CI

## Scope
### Must have
1. Commit pending fixes (installment=1, isRedirect logic, README docs)
2. Unit tests for all 12 existing Message classes + Gateway (tests/Message/ filled, GatewayTest expanded)
3. InstallmentInfo + BinNumber feature (lookup endpoint)
4. Card Storage feature (create + delete + list user cards)
5. PayWithIyzico (PWI) feature (initialize + retrieve)
6. AcceptNotification / webhook support
7. Response.php field completeness
8. GitHub Actions CI (PHP 8.1/8.2/8.3)

### Must NOT have (guardrails, anti-slop, scope boundaries)
- No subscription management system
- No marketplace/submerchant operations
- No IyzicoLink product management
- No reporting API integration
- No BKM Express
- No APM (Alternative Payment Methods)
- No physical POS / Terminal API
- No breaking changes to existing public API signatures
- No real end-to-end tests requiring sandbox credentials
- No changes to vendor/ directory

## Verification strategy
> Zero human intervention - all verification is agent-executed.
- Test decision: tests-after for existing code, TDD for new features
- Framework: PHPUnit 10/11
- Evidence: .omo/evidence/task-<N>-comprehensive-feature-completion.<ext>
- All tests pass: `vendor/bin/phpunit --no-coverage`
- Coverage check: `vendor/bin/phpunit --coverage-text` (target 80%+)

## Execution strategy
### Parallel execution waves
- Wave 1: Commit fixes (1 todo) — fast, standalone
- Wave 2: Tests for existing Message classes (4 parallel — grouped by similarity)
- Wave 3: Tests for Gateway + remaining classes (3 parallel)
- Wave 4: New features — InstallmentInfo/BIN + Card Storage (2 parallel)
- Wave 5: New features — PWI + AcceptNotification (2 parallel)
- Wave 6: Response polish + CI (2 parallel)
- Wave 7: Final verification wave

### Dependency matrix
| Todo | Depends on | Blocks | Can parallelize with |
| --- | --- | --- | --- |
| T1 (commit fixes) | — | — | — |
| T2 (AbstractRequest tests) | T1 | — | T3, T4, T5 |
| T3 (PurchaseRequest tests) | T1 | — | T2, T4, T5 |
| T4 (AuthorizeRequest tests) | T1 | — | T2, T3, T5 |
| T5 (Capture/Refund/Void tests) | T1 | — | T2, T3, T4 |
| T6 (FetchTransaction/Checkout tests) | T1 | — | T7, T8 |
| T7 (CheckoutStatus/CompletePurchase tests) | T1 | — | T6, T8 |
| T8 (Gateway + Response tests) | T1 | — | T6, T7 |
| T9 (InstallmentInfo/BIN feature) | T1 | — | T10, T11 |
| T10 (CardStorage feature) | T1 | — | T9, T11 |
| T11 (PWI feature) | T1 | — | T9, T10 |
| T12 (AcceptNotification) | T1 | T13 | T9, T10, T11 |
| T13 (Response.php fields + polish) | T12 | — | T14 |
| T14 (GitHub Actions CI) | — | — | T13 |

## Todos
> Implementation + Test = ONE todo. Never separate.
<!-- APPEND TASK BATCHES BELOW THIS LINE WITH edit/apply_patch - never rewrite the headers above. -->

### Wave 1 — Commit pending fixes
- [x] 1. Commit uncommitted fixes
  What to do / Must NOT do: Commit the current uncommitted changes in src/Gateway.php, src/Message/RedirectResponse.php, and README.md. These fix installment default (0→1), isRedirect() logic (only redirect when status=success + has content), and expand README with test cards, important notes, and API quirks. Write a conventional commit message summarizing all three changes. Do NOT modify any files — just stage and commit.
  Parallelization: Wave 1 | Blocked by: — | Blocks: all subsequent
  References:
  - git diff HEAD: src/Gateway.php:37 (installment 0→1)
  - git diff HEAD: src/Message/RedirectResponse.php:31-35 (isRedirect logic)
  - git diff HEAD: README.md (test cards, notes, quirks)
  Acceptance criteria: `git log --oneline -1` shows a conventional commit message with the three fixes. `git status --porcelain` is clean.
  QA scenarios: Verify `git log --oneline -1` returns a commit, verify `git diff HEAD` is empty.
  Commit: Y | `fix: correct installment default, isRedirect logic, and update README with docs/quirk notes`

### Wave 2 — Message tests (group 1)
- [x] 2. AbstractRequest unit tests
  What to do / Must NOT do: Write Omnipay\Iyzico\Tests\Message\AbstractRequestTest that tests:
  - getApiKey/setApiKey, getSecretKey/setSecretKey, getConversationId (auto-generates when empty), getPaymentId/setPaymentId
  - mapLocale maps TR→IyzicoLocale::TR, EN→IyzicoLocale::EN, others→TR
  - mapCurrency correctly maps TRY/TL/EUR/USD/GBP/RUB/IRR/NOK/CHF
  - mapPaymentChannel maps WEB/MOBILE/MOBILE_WEB
  - mapPaymentGroup maps PRODUCT/LISTING/SUBSCRIPTION
  - generateConversationId returns non-empty string starting with 'txn_'
  - buildBuyer creates Iyzipay\Model\Buyer with correct fields from CreditCard
  - buildShippingAddress, buildBillingAddress create Address objects with correct fields
  - buildPaymentCard creates PaymentCard with correct data
  - buildBasketItems creates array from items parameter, falls back to single item
  - createIyzicoOptions returns Options with apiKey/secretKey/baseUrl set
  Since AbstractRequest is abstract, create a concrete test double that extends it with minimal sendData().
  Parallelization: Wave 2 | Blocked by: T1 | Blocks: — | Can parallelize with: T3, T4, T5
  References: src/Message/AbstractRequest.php (all methods)
  Acceptance criteria: `vendor/bin/phpunit tests/Message/AbstractRequestTest.php` passes with 0 failures/errors.
  QA scenarios: Run `vendor/bin/phpunit tests/Message/AbstractRequestTest.php`
  Commit: N (batched after Wave 3)

- [x] 3. PurchaseRequest unit tests
  What to do / Must NOT do: Write Omnipay\Iyzico\Tests\Message\PurchaseRequestTest that tests:
  - getData() validates card + amount; throws InvalidRequestException when missing
  - getData() returns correct array structure with locale, conversationId, price, paidPrice, currency, installment, paymentChannel, paymentGroup, callbackUrl, secure3d, card, clientIp
  - sendData() with secure3d=false calls Payment::create and returns Response
  - sendData() with secure3d=true+calls ThreedsInitialize::create and returns RedirectResponse
  - Uses PHPUnit mocking for Iyzipay\Model\Payment and ThreedsInitialize static methods
  Must NOT call real iyzico API. Mock the SDK model classes.
  Parallelization: Wave 2 | Blocked by: T1 | Blocks: — | Can parallelize with: T2, T4, T5
  References: src/Message/PurchaseRequest.php, src/Message/AbstractRequest.php:13-267
  Acceptance criteria: `vendor/bin/phpunit tests/Message/PurchaseRequestTest.php` passes.
  QA scenarios: Run `vendor/bin/phpunit tests/Message/PurchaseRequestTest.php`
  Commit: N (batched after Wave 3)

- [x] 4. AuthorizeRequest unit tests
  What to do / Must NOT do: Write Omnipay\Iyzico\Tests\Message\AuthorizeRequestTest. Similar structure to PurchaseRequest but:
  - sendData() with secure3d=false calls PaymentPreAuth::create
  - sendData() with secure3d=true calls ThreedsInitialize::create
  - getData() validates card + amount
  Must NOT call real iyzico API. Mock the SDK model classes.
  Parallelization: Wave 2 | Blocked by: T1 | Blocks: — | Can parallelize with: T2, T3, T5
  References: src/Message/AuthorizeRequest.php
  Acceptance criteria: `vendor/bin/phpunit tests/Message/AuthorizeRequestTest.php` passes.
  QA scenarios: Run `vendor/bin/phpunit tests/Message/AuthorizeRequestTest.php`
  Commit: N (batched after Wave 3)

- [x] 5. CaptureRequest + RefundRequest + VoidRequest unit tests
  What to do / Must NOT do: Write three test files:
  - CaptureRequestTest: tests getData() validates paymentId, sends CreatePaymentPostAuthRequest via PaymentPostAuth::create
  - RefundRequestTest: tests getData() validates paymentTransactionId+conversationId+amount, sends CreateRefundRequest via Refund::create, tests getPaymentTransactionId/setPaymentTransactionId, getReason/setReason
  - VoidRequestTest: tests getData() validates paymentId+conversationId, sends CreateCancelRequest via Cancel::create, tests getReason/setReason
  Must NOT call real iyzico API. Mock SDK model classes.
  Parallelization: Wave 2 | Blocked by: T1 | Blocks: — | Can parallelize with: T2, T3, T4
  References: src/Message/CaptureRequest.php, src/Message/RefundRequest.php, src/Message/VoidRequest.php
  Acceptance criteria: `vendor/bin/phpunit tests/Message/CaptureRequestTest.php tests/Message/RefundRequestTest.php tests/Message/VoidRequestTest.php` all pass.
  QA scenarios: Run the three test files.
  Commit: N (batched after Wave 3)

### Wave 3 — Message tests (group 2) + Gateway + Response
- [x] 6. FetchTransactionRequest + CheckoutRequest unit tests
  What to do / Must NOT do: Write two test files:
  - FetchTransactionRequestTest: getData() validates paymentId+conversationId, calls Payment::retrieve
  - CheckoutRequestTest: getData() validates amount, sends CreateCheckoutFormInitializeRequest via CheckoutFormInitialize::create, returns RedirectResponse with getRedirectUrl() = paymentPageUrl, tests getBasketId/setBasketId, getEnabledInstallments/setEnabledInstallments
  - Must NOT mock getPaymentPageUrl — check getRedirectUrl from the response object.
  Parallelization: Wave 3 | Blocked by: T1 | Blocks: — | Can parallelize with: T7, T8
  References: src/Message/FetchTransactionRequest.php, src/Message/CheckoutRequest.php
  Acceptance criteria: `vendor/bin/phpunit tests/Message/FetchTransactionRequestTest.php tests/Message/CheckoutRequestTest.php` passes.
  QA scenarios: Run both test files.
  Commit: N (batched after Wave 3)

- [x] 7. CheckoutStatusRequest + CompletePurchaseRequest unit tests
  What to do / Must NOT do: Write two test files:
  - CheckoutStatusRequestTest: getData() validates token, calls CheckoutForm::retrieve, tests getToken/setToken
  - CompletePurchaseRequestTest: getData() validates paymentId+conversationData, calls ThreedsPayment::create, tests getConversationData/setConversationData
  Parallelization: Wave 3 | Blocked by: T1 | Blocks: — | Can parallelize with: T6, T8
  References: src/Message/CheckoutStatusRequest.php, src/Message/CompletePurchaseRequest.php
  Acceptance criteria: `vendor/bin/phpunit tests/Message/CheckoutStatusRequestTest.php tests/Message/CompletePurchaseRequestTest.php` passes.
  QA scenarios: Run both test files.
  Commit: N (batched after Wave 3)

- [x] 8. Gateway + Response + RedirectResponse unit tests
  What to do / Must NOT do: Expand GatewayTest and add ResponseTest + RedirectResponseTest:
  - GatewayTest additions: testGetDefaultParameters full structure, testSetTestMode switches baseUrl, testGetConversationId, testGetPaymentChannel, testGetPaymentGroup, testAuthorize/capture/refund/void/fetchTransaction/checkout/checkoutStatus/completePurchase all return correct request classes
  - ResponseTest: testIsSuccessful (status=success), testIsPending (status=pending), testGetMessage with errorMessage+errorCode, testGetTransactionReference (paymentId > conversationId), testGetCheckoutFormContent, testNormalizeData with object (reads IYZICO_FIELDS via getters), testNormalizeData with array, testNormalizeData with string (JSON decode), testNormalizeData with null (empty array)
  - RedirectResponseTest: testIsRedirect (status=success + has htmlContent), testIsRedirect without htmlContent returns false, testGetHtmlContent reads htmlContent then checkoutFormContent, testSetRedirectUrl/getRedirectUrl, testSetRedirectMethod/getRedirectMethod, testSetRedirectData/getRedirectData
  Parallelization: Wave 3 | Blocked by: T1 | Blocks: — | Can parallelize with: T6, T7
  References: src/Gateway.php, src/Message/Response.php, src/Message/RedirectResponse.php, tests/GatewayTest.php
  Acceptance criteria: `vendor/bin/phpunit tests/GatewayTest.php tests/Message/ResponseTest.php tests/Message/RedirectResponseTest.php` passes.
  QA scenarios: Run the three test files.
  Commit: Y (batch commit) | `test: add comprehensive unit tests for all message classes`

### Wave 4 — New core features (group 1)
- [x] 9. InstallmentInfo + BinNumber feature
  What to do / Must NOT do: Create two new request/response pairs under Omnipay\Iyzico\Message:
  - `BinNumberRequest` extends AbstractRequest:
    - getData() validates 'binNumber' parameter
    - sendData() creates RetrieveBinNumberRequest, calls BinNumber::retrieve(), returns Response
    - getBinNumber/setBinNumber parameter accessors
  - `InstallmentRequest` extends AbstractRequest:
    - getData() validates 'binNumber' parameter
    - sendData() creates RetrieveInstallmentInfoRequest, calls InstallmentInfo::retrieve(), returns Response
    - getBinNumber/setBinNumber parameter accessors
  - Add `fetchBinNumber(array)` and `fetchInstallment(array)` methods to Gateway.php that create the respective requests.
  - Add getter/setter for 'binNumber' on AbstractRequest or the specific requests.
  - Update Response.php IYZICO_FIELDS to include fields returned by BinNumber (cardType, cardAssociation, cardFamily, bankName, bankCode, commercial) and InstallmentInfo (installmentDetails).
  - Write BinNumberRequestTest + InstallmentRequestTest using mocked SDK.
  Must NOT: Hardcode field lists that don't exist; only add fields the SDK actually returns.
  Parallelization: Wave 4 | Blocked by: T1 | Blocks: — | Can parallelize with: T10, T11
  References:
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Model/BinNumber.php
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Model/InstallmentInfo.php
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Request/RetrieveBinNumberRequest.php
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Request/RetrieveInstallmentInfoRequest.php
  - src/Gateway.php (add methods pattern follows existing fetchTransaction)
  Acceptance criteria: `vendor/bin/phpunit tests/Message/BinNumberRequestTest.php tests/Message/InstallmentRequestTest.php` passes.
  QA scenarios: Run both test files.
  Commit: N (batched after Wave 5)

- [x] 10. Card Storage feature
  What to do / Must NOT do: Create three new request/response pairs:
  - `CreateCardRequest` extends AbstractRequest:
    - getData() validates 'card' (CreditCard-like), 'email', 'cardUserKey'
    - sendData() calls Card::create(), returns Response
    - getCardUserKey/setCardUserKey, getEmail/setEmail accessors
  - `DeleteCardRequest` extends AbstractRequest:
    - getData() validates 'cardToken', 'cardUserKey'
    - sendData() calls Card::delete(), returns Response
    - getCardToken/setCardToken accessors
  - `ListCardsRequest` extends AbstractRequest:
    - getData() validates 'cardUserKey'
    - sendData() creates RetrieveCardListRequest, calls CardList::retrieve(), returns Response
    - getCardUserKey/setCardUserKey accessors
  - Add `createCard(array)`, `deleteCard(array)`, `listCards(array)` to Gateway.php.
  - Update AbstractRequest with getCardUserKey/setCardUserKey, getCardToken/setCardToken, getEmail/setEmail if not present.
  - Update Response.php IYZICO_FIELDS with card-storage fields (externalId, cardUserKey, cardToken, cardAlias, cardBankCode, cardBankName).
  - Write CreateCardRequestTest, DeleteCardRequestTest, ListCardsRequestTest using mocked SDK.
  Must NOT: Actually store any card data in the gateway; just pass through to iyzico API.
  Parallelization: Wave 4 | Blocked by: T1 | Blocks: — | Can parallelize with: T9, T11
  References:
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Model/Card.php
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Model/CardList.php
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Request/CreateCardRequest.php
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Request/DeleteCardRequest.php
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Request/RetrieveCardListRequest.php
  Acceptance criteria: `vendor/bin/phpunit tests/Message/CreateCardRequestTest.php tests/Message/DeleteCardRequestTest.php tests/Message/ListCardsRequestTest.php` passes.
  QA scenarios: Run all three test files.
  Commit: N (batched after Wave 5)

### Wave 5 — New core features (group 2)
- [x] 11. PayWithIyzico (PWI) feature
  What to do / Must NOT do: Create two new request classes:
  - `PayWithIyzicoInitializeRequest` extends AbstractRequest:
    - getData() validates 'amount', 'returnUrl'
    - Data includes: locale, conversationId, price, paidPrice, currency, basketId, paymentGroup, buyer, shippingAddress, billingAddress, basketItems, callbackUrl, paymentSource, currency (from request params)
    - sendData() creates CreatePayWithIyzicoInitializeRequest, calls PayWithIyzicoInitialize::create(), returns RedirectResponse with paymentPageUrl
    - getBasketId/setBasketId, getEnabledInstallments/setEnabledInstallments accessors
    - Reuse buildBuyer/buildShippingAddress/buildBillingAddress/buildBasketItems from AbstractRequest
  - `PayWithIyzicoRetrieveRequest` extends AbstractRequest:
    - getData() validates 'token'
    - sendData() creates RetrievePayWithIyzicoRequest, calls PayWithIyzico::retrieve(), returns Response
    - getToken/setToken accessor
  - Add `payWithIyzico(array)` and `payWithIyzicoStatus(array)` to Gateway.php.
  - Update Response.php IYZICO_FIELDS with PWI-specific fields (token, callbackUrl, paymentStatus, signature from PayWithIyzico; paymentPageUrl, checkoutFormContent from PayWithIyzicoInitializeResource).
  - Write PayWithIyzicoInitializeRequestTest + PayWithIyzicoRetrieveRequestTest using mocked SDK.
  Must NOT: Confuse PWI with CheckoutForm — they use different SDK classes and different endpoints.
  Parallelization: Wave 5 | Blocked by: T1 | Blocks: — | Can parallelize with: T9, T10
  References:
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Model/PayWithIyzicoInitialize.php
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Model/PayWithIyzico.php
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Request/CreatePayWithIyzicoInitializeRequest.php
  - vendor/iyzico/iyzipay-php/src/Iyzipay/Request/RetrievePayWithIyzicoRequest.php
  - src/Message/CheckoutRequest.php (pattern to follow)
  Acceptance criteria: `vendor/bin/phpunit tests/Message/PayWithIyzicoInitializeRequestTest.php tests/Message/PayWithIyzicoRetrieveRequestTest.php` passes.
  QA scenarios: Run both test files.
  Commit: N (batched after Wave 5)

- [x] 12. AcceptNotification / webhook support
  What to do / Must NOT do: Add webhook/notification handling:
  - Create `AcceptNotificationRequest` implementing Omnipay\Common\Message\NotificationInterface:
    - Takes raw $_POST data (or request parameters) containing iyzico callback
    - getTransactionReference() extracts paymentId from data
    - getTransactionStatus() returns TransactionStatus based on mdStatus and status
    - getMessage() returns errorMessage or success message
    - Uses IyzicoPayment::create() (ThreedsPayment) or Payment::retrieve() depending on context
  - Alternatively, keep it simple: Create a helper that the user calls with the callback request data, and it determines the payment status. Do NOT create a full request/response class unless it maps cleanly to Omnipay's NotificationInterface.
  - Add `acceptNotification(array)` to Gateway.php that returns AcceptNotificationRequest.
  - Write tests for status determination with various mdStatus values.
  - Must NOT break existing CompletePurchase flows.
  Parallelization: Wave 5 | Blocked by: T1 | Blocks: T13 | Can parallelize with: T9, T10, T11
  References:
  - Omnipay Common NotificationInterface (vendor/omnipay/common/src/Common/Message/NotificationInterface.php)
  - Omnipay\Common\Message\NotificationInterface: getTransactionReference(), getTransactionStatus(), getMessage()
  - src/Message/CompletePurchaseRequest.php (existing 3DS completion pattern)
  Acceptance criteria: `vendor/bin/phpunit tests/Message/AcceptNotificationRequestTest.php` passes.
  QA scenarios: Run the test file.
  Commit: N (batched with T13)

### Wave 6 — Polish + CI
- [x] 13. Response field completeness + polish
  What to do / Must NOT do: Update Response.php:
  - Add new IYZICO_FIELDS from all current feature responses: BinNumber fields (cardType, cardAssociation, cardFamily, bankName, bankCode, commercial), InstallmentInfo (installmentDetails), Card storage (externalId, cardUserKey, cardToken, cardAlias, cardBankCode, cardBankName), PWI (token, callbackUrl, paymentStatus, paymentPageUrl, checkoutFormContent, signature)
  - Ensure normalizeData handles the union of all fields across all SDK models
  - Add any missing generic response accessors
  - Merge the batch commit with T12
  Must NOT: Remove any existing fields from IYZICO_FIELDS.
  Parallelization: Wave 6 | Blocked by: T12 | Blocks: — | Can parallelize with: T14
  References: src/Message/Response.php:18-25
  Acceptance criteria: All existing tests still pass. `vendor/bin/phpunit` shows green.
  QA scenarios: Run full test suite.
  Commit: Y (batch with T12) | `feat: add Installment/BIN/CardStorage/PWI features and webhook support`

- [x] 14. GitHub Actions CI
  What to do / Must NOT do: Create .github/workflows/ci.yml:
  - Trigger: push, pull_request on master/main
  - Matrix: PHP 8.1, 8.2, 8.3
  - Steps: checkout, setup PHP with ini values, composer install --no-progress, vendor/bin/phpunit
  - Add composer.json scripts section for `test` and `test-coverage` if not already present
  - Do NOT require coverage thresholds in CI (coverage tools need pcov/xdebug which may not be available)
  - Do NOT commit CI secrets or API keys
  Parallelization: Wave 6 | Blocked by: — | Blocks: — | Can parallelize with: T13
  References: (standard GitHub Actions PHP workflow)
  Acceptance criteria: `.github/workflows/ci.yml` exists with valid YAML. `php -l .github/workflows/ci.yml` reports no syntax error (YAML valid). Cannot run the actual workflow without pushing, but validate YAML structure.
  QA scenarios: Verify file exists, parse YAML syntax.
  Commit: Y | `ci: add GitHub Actions workflow for PHP 8.1/8.2/8.3`

## Final verification wave
> Runs in parallel after ALL todos. ALL must APPROVE. Surface results and wait for the user's explicit okay before declaring complete.
- [x] F1. Plan compliance audit — verify every todo's acceptance criteria is met
- [x] F2. Code quality review — run PHPCS/PHPStan if available, review all new code patterns
- [x] F3. Full test suite — `vendor/bin/phpunit` all green with 0 failures/errors/risky
- [x] F4. Scope fidelity — confirm no Must NOT have items were implemented

## Commit strategy
| Commit | Message | Files |
|---|---|---|
| After T1 | `fix: correct installment default, isRedirect logic, and update README with docs/quirk notes` | src/Gateway.php, src/Message/RedirectResponse.php, README.md |
| After T2-T8 | `test: add comprehensive unit tests for all message classes` | tests/Message/*.php, tests/GatewayTest.php |
| After T9-T13 | `feat: add Installment/BIN/CardStorage/PWI features and webhook support` | src/Message/{new files}, src/Gateway.php, src/Message/AbstractRequest.php, src/Message/Response.php |
| After T14 | `ci: add GitHub Actions workflow for PHP 8.1/8.2/8.3` | .github/workflows/ci.yml |

## Success criteria
1. `git status --porcelain` is clean with all changes committed
2. `vendor/bin/phpunit` passes with 0 failures/errors
3. Coverage >= 80% (all message classes + Gateway under test)
4. Gateway has new methods: fetchBinNumber, fetchInstallment, createCard, deleteCard, listCards, payWithIyzico, payWithIyzicoStatus, acceptNotification
5. README documents new features
6. .github/workflows/ci.yml exists with valid PHP matrix workflow
