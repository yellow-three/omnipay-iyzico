---
slug: comprehensive-feature-completion
status: awaiting-approval
intent: unclear
pending-action: write .omo/plans/comprehensive-feature-completion.md
approach: Complete the omnipay-iyzico package with tests, missing features, and quality improvements
---

# Draft: comprehensive-feature-completion

## Components (topology ledger)
<!-- Lock the SHAPE before depth. One row per top-level component that can succeed or fail independently. -->
<!-- id | outcome (one line) | status: active|deferred | evidence path -->

| id | outcome | status | evidence |
|---|---|---|---|
|gateway|Gateway CRUD operations + checkout + checkoutStatus + completePurchase implemented|active|src/Gateway.php|
|abstract-request|Abstract request with builder helpers for buyer/address/paymentCard/basketItems|active|src/Message/AbstractRequest.php|
|purchase|PurchaseRequest with 3DS + non-3DS|active|src/Message/PurchaseRequest.php|
|authorize|AuthorizeRequest with pre-auth (3DS + non-3DS)|active|src/Message/AuthorizeRequest.php|
|capture|CaptureRequest (post-auth)|active|src/Message/CaptureRequest.php|
|refund|RefundRequest|active|src/Message/RefundRequest.php|
|void|VoidRequest (cancel)|active|src/Message/VoidRequest.php|
|fetch-transaction|FetchTransactionRequest (payment detail)|active|src/Message/FetchTransactionRequest.php|
|checkout|CheckoutRequest (checkout form initialize)|active|src/Message/CheckoutRequest.php|
|checkout-status|CheckoutStatusRequest (checkout form retrieve)|active|src/Message/CheckoutStatusRequest.php|
|complete-purchase|CompletePurchaseRequest (3DS auth)|active|src/Message/CompletePurchaseRequest.php|
|response|Response + RedirectResponse with HTML content|active|src/Message/Response.php, src/Message/RedirectResponse.php|
|tests-gateway|GatewayTest has parameter + request-creation tests|active|tests/GatewayTest.php|
|tests-message|tests/Message directory is EMPTY|missing|tests/Message/|
|installment-bin|InstallmentInfo + BinNumber not implemented|deferred|vendor lib has Iyzipay\Model\InstallmentInfo, BinNumber|
|card-storage|Card create/delete/list not implemented|deferred|vendor lib has Iyzipay\Model\Card, CardList|
|pwi|PayWithIyzico not implemented|deferred|vendor lib has Iyzipay\Model\PayWithIyzico, PayWithIyzicoInitialize|
|apm|Alternative Payment Methods not implemented|deferred|vendor lib has Iyzipay\Model\Apm|
|bkm|BKM Express not implemented|deferred|vendor lib has Iyzipay\Model\Bkm, BkmInitialize|
|subscription|Full subscription system not implemented|deferred|vendor lib has Iyzipay\Model\Subscription/* (16 classes)|
|marketplace|SubMerchant + Approval + Disapproval not implemented|deferred|vendor lib has SubMerchant, Approval, Disapproval|
|iyzico-link|IyziLink product management not implemented|deferred|vendor lib has Iyzipay\Model\Iyzilink/* (12 classes)|
|reporting|ReportingPaymentDetail + ReportingPaymentTransaction not implemented|deferred|vendor lib has both|
|settlement|SettlementToBalance + RefundToBalance not implemented|deferred|vendor lib has both|
|examples|9 example scripts exist in example/|active|example/|

## Open assumptions (announced defaults)
<!-- Intent is UNCLEAR: research resolves ambiguity, defaults are adopted (not asked), and each is surfaced in the plan's human TL;DR for veto. -->
<!-- assumption | adopted default | rationale | reversible? -->

| assumption | adopted default | rationale | reversible? |
|---|---|---|---|
|Plan scope|Focus on: 1) commit pending fixes, 2) core feature parity (installment/BIN, card storage, PWI), 3) comprehensive tests|The current code has uncommitted fixes, missing tests, and missing but important features|Yes - user can add/remove features|
|Feature priority|Installment/BIN, Card Storage, PWI are first-class features; Subscription/Marketplace/IyzicoLink are "future" tier|Usage frequency: BIN check and card storage are most commonly needed after core payments|Yes|
|Test strategy|Tests-after (not TDD) for existing code; TDD for new features|Existing code is already written; new features benefit from TDD|No|
|Response completeness|Response.php IYZICO_FIELDS needs updating for new feature responses|New SDK models return fields not covered by current field list|Yes|

## Findings (cited - path:lines)

### Current state
- Gateway.php: 201 lines, 15 gateway methods (purchase, authorize, capture, refund, void, fetchTransaction, checkout, checkoutStatus, completePurchase) + getters/setters (src/Gateway.php)
- AbstractRequest.php: Builder methods for Buyer, Shipping/Billing Address, PaymentCard, BasketItems + locale/currency/channel/group mappers (src/Message/AbstractRequest.php)
- PurchaseRequest.php: 3DS via ThreedsInitialize::create(), non-3DS via Payment::create() (src/Message/PurchaseRequest.php:58-68)
- Response.php: normalizeData() reads 25 IYZICO_FIELDS via getters from SDK model objects (src/Message/Response.php:18-25)
- RedirectResponse.php: isRedirect() checks status=success AND (redirectUrl OR htmlContent present) (src/Message/RedirectResponse.php:31-35)
- Tests: GatewayTest.php has 9 tests (137 lines), tests/Message/ is EMPTY

### Pending uncommitted changes (git diff HEAD)
- README.md: Major expansion with test cards, important notes, iyzico API quirks
- Gateway.php: installment default changed from 0 to 1
- RedirectResponse.php: isRedirect() now returns true only when status=success AND has redirect content

### Available vendor SDK features NOT in omnipay
- `Iyzipay\Model\InstallmentInfo::retrieve()` + `RetrieveInstallmentInfoRequest` — BIN/installment query
- `Iyzipay\Model\BinNumber::retrieve()` + `RetrieveBinNumberRequest` — BIN check
- `Iyzipay\Model\Card::create()`, `Card::delete()`, `CardList::retrieve()` — card storage
- `Iyzipay\Model\PayWithIyzicoInitialize::create()`, `PayWithIyzico::retrieve()` — PWI
- `Iyzipay\Model\BkmInitialize::create()`, `Bkm::retrieve()` — BKM Express
- `Iyzipay\Model\Apm::create()`, `Apm::retrieve()` — alternative payment methods
- `Iyzipay\Model\Subscription\*` — 16 classes for subscription management
- `Iyzipay\Model\SubMerchant`, `Approval`, `Disapproval` — marketplace
- `Iyzipay\Model\Iyzilink\*` — 12 classes for Iyzico Link
- `Iyzipay\Model\ReportingPaymentDetail`, `ReportingPaymentTransaction` — reporting
- `Iyzipay\Model\SettlementToBalance`, `RefundToBalance` — balance operations
- `Iyzipay\Model\CrossBookingFromSubMerchant`, `CrossBookingToSubMerchant` — cross booking
- `Iyzipay\Model\Loyalty` — loyalty points
- `Iyzipay\Model\ProtectedOverleyScript` — 3DS overlay protection
- `Iyzipay\Model\PlusInstallmentPayment` — bonus installment

### Documentation references
- iyzico API docs: docs.iyzico.com (TR + EN) — complete API reference
- iyzico PHP SDK: vendor/iyzico/iyzipay-php/src/Iyzipay/ — 102 Model classes, 49 Request classes

## Decisions (with rationale)

1. **Phase 1: Commit pending fixes** — The current uncommitted changes fix real bugs (installment=0 invalid, isRedirect(), README docs). These should be committed first as a baseline.
2. **Phase 2: Message-level unit tests** — tests/Message/ is empty. Every request class needs sendData/getData tests using mocked SDK to avoid real API calls.
3. **Phase 3: Core missing features** — InstallmentInfo/BinNumber (most requested), Card Storage (second), PayWithIyzico (third). These are the most commonly needed features beyond basic payments.
4. **Phase 4: Optional features** — APM, BKM, Subscription, Marketplace, IyzicoLink, Reporting, Settlement. These are important but less commonly needed; documented for future reference.
5. **Use mock-heavy tests** — No real API keys in tests. Mock the iyzico SDK model classes to simulate success/failure responses.

## Scope IN
- Commit pending uncommitted fixes (installment, isRedirect, README)
- Add comprehensive unit tests for all 12 Message classes + Gateway
- Add InstallmentInfo + BinNumber feature (Omnipay-style request/response)
- Add Card Storage feature (create/delete/list cards)
- Add PayWithIyzico (PWI) feature (initialize + retrieve)
- Add basic webhook/notification support (acceptNotification)
- Polish: better error messages, edge case handling
- Update Response.php IYZICO_FIELDS for new feature responses
- CI config (GitHub Actions) for PHP 8.1/8.2/8.3

## Scope OUT (Must NOT have)
- Subscription management system (deferred to future)
- Full marketplace/submerchant (deferred)
- Iyzico Link product management (deferred)
- Reporting API (deferred)
- Physical POS / Terminal API (completely separate domain)
- BKM Express (deferred)
- APM / CrossBooking / SettlementToBalance (deferred)
- Breaking changes to existing public API signatures
- Real end-to-end tests against sandbox (requires API keys)

## Open questions
(Intent is unclear - best-practice defaults adopted for all, documented above)

## Approval gate
status: awaiting-approval
<!-- When exploration is exhausted and unknowns are answered, set status: awaiting-approval. -->
<!-- That durable record is the loop guard: on a later turn read it and resume at the gate instead of re-running exploration. -->
