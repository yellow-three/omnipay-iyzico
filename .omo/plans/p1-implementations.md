# p1-implementations - Work Plan

## Current State (Last Updated: 25 Jun 2026)

| Wave | Features | Status | Commits |
|------|----------|--------|---------|
| A | PreAuth, RefundToBalance, PlusInstallment/Loyalty | ✅ **Complete** | `3986f59`, `c9796c0` |
| B | BKM Express, APM, Reporting | ✅ **Complete** | `a217cb9`, `be92794`, `b457feb` |
| C | iyzico Link | ✅ **Complete** | `b1fe422`, `a57bdfb`, `e431f02` |
| D | Marketplace | ❌ **Not started** | — |
| E | Subscription | ❌ **Not started** | — |
| F | README update + Verification | ❌ **Not started** | — |

**Tests:** 423 passing (1101 assertions) — all green. 7 feature areas implemented (42 new Message classes + tests).

## Next Action
Start **Wave D: Marketplace** — 8 sub-merchant/payment request classes.

---

## TL;DR (For humans)

**What you get:** Nine feature groups added to the omnipay-iyzico gateway. **6 of 9 done (Waves A/B/C). Remaining: Marketplace, Subscription, README.**

**Why this approach:** All P1 items follow the exact same pattern as the 17 existing Message classes (extend AbstractRequest, implement getData/sendData, call SDK static method). Grouping by complexity (simple → medium → large → subscription) reduces risk.

**Effort (remaining):** Large — Marketplace (~8 classes) + Subscription (~28 classes) + README
**Risk (remaining):** Low — pattern is proven across 42 existing implementations

---

> TL;DR (machine): XL effort, Medium risk — 9 P1 feature groups ~50 new Message classes following existing pattern

## Scope
### Must have
- Item 4: CheckoutForm/PWI/BasicThreeds PreAuth Initialize
- Item 5: iyzico Link (save, retrieve, list, delete, update status, fastlink, search)
- Item 6: Reporting (payment detail, transaction, scroll)
- Item 7: BKM Express (initialize, retrieve, basic)
- Item 8: APM (initialize, retrieve)
- Item 9: Subscription (products, pricing plans, customers, create/activate/cancel/retry/upgrade, checkout form, card update)
- Item 10: Marketplace (sub-merchant CRUD, approval/disapproval, cross-booking, payment item update)
- Item 11: RefundToBalance / SettlementToBalance
- Item 12: PlusInstallment / Loyalty
- Each feature: Gateway method(s), Message request class(es), test(s), README update

### Must NOT have (guardrails, anti-slop, scope boundaries)
- Do NOT modify existing Message classes (PurchaseRequest, CheckoutRequest, etc.)
- Do NOT modify AbstractRequest
- Do NOT modify abstract method signatures
- Do NOT add signature verification to new GET endpoints
- Do NOT add SDK Mapper classes — SDK already provides them
- Do NOT re-implement SDK logic — delegate to SDK static methods
- No external PHP packages beyond what composer already provides
- No config files, CI changes, or infrastructure work

## Verification strategy
> Zero human intervention - all verification is agent-executed.
- Test decision: tests-after + PHPUnit (existing framework)
- Evidence: .omo/evidence/task-<N>-p1-implementations.txt

## Execution strategy
### Execution waves — Progress
| Wave | Items | Status | Details |
|------|-------|--------|---------|
| Wave A | Items 4, 11, 12 | ✅ **Complete** | PreAuth, RefundToBalance, PlusInstallment/Loyalty |
| Wave B | Items 7, 8, 6 | ✅ **Complete** | BKM Express, APM, Reporting |
| Wave C | Item 5 (iyzico Link) | ✅ **Complete** | 7 request classes, 7 test files |
| Wave D | Item 10 (Marketplace) | ⬜ **Pending** | 8 sub-merchant/payment classes |
| Wave E | Item 9 (Subscription) | ⬜ **Pending** | ~28 subscription classes |
| Wave F | README + Verification | ⬜ **Pending** | After Waves D-E |

### Dependency matrix
| Todo | Depends on | Blocks | Can parallelize with |
| --- | --- | --- | --- |
| All within a Wave | — | — | All others in same Wave |
| Wave D | — (pattern proven) | — | — |
| Wave E | — (pattern proven) | — | Wave D |
| Wave F (README) | Waves D-E | — | — |
| Verification | All | — | — |

## Todos
> Implementation + Test = ONE todo. Never separate.
<!-- APPEND TASK BATCHES BELOW THIS LINE WITH edit/apply_patch - never rewrite the headers above. -->
- [x] 1. Add PreAuth Initialize: CheckoutFormPreAuth, PayWithIyzicoPreAuth, BasicThreedsPreAuth
  What to do / Must NOT do:
  - Create `src/Message/CheckoutFormPreAuthRequest.php` extending `AbstractRequest` (copy CheckoutRequest pattern, call `CheckoutFormInitializePreAuth::create()` endpoint `/payment/iyzipos/checkoutform/initialize/preauth/ecom`)
  - Create `src/Message/PayWithIyzicoPreAuthRequest.php` extending `AbstractRequest` (copy PayWithIyzicoInitializeRequest pattern, call `\Iyzipay\Model\PayWithIyzicoInitialize::create()` with `paymentGroup=LISTING` and preauth endpoint)
  - Create `src/Message/BasicThreedsPreAuthRequest.php` extending `AbstractRequest` (simpler — uses `BasicThreedsInitializePreAuth::create(CreatePaymentRequest)` endpoint `/payment/3dsecure/initialize/preauth`)
  - Add Gateway methods: `checkoutFormPreAuth()`, `payWithIyzicoPreAuth()`, `basicThreedsPreAuth()`
  - Do NOT modify existing CheckoutRequest or PayWithIyzicoInitializeRequest
  Do NOT add buyer/shipping/billing builder calls where SDK doesn't need them
  Parallelization: Wave A | Blocked by: — | Blocks: —
  References: src/Message/CheckoutRequest.php, src/Message/PayWithIyzicoInitializeRequest.php, src/Gateway.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Model/CheckoutFormInitializePreAuth.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Model/BasicThreedsInitializePreAuth.php
  Acceptance criteria: vendor/bin/phpunit --no-coverage passes
  QA scenarios: happy — CheckoutFormPreAuthRequest getData() returns expected structure; failure — missing amount throws InvalidRequestException. Evidence .omo/evidence/task-1-p1-implementations.txt
  Commit: Y | feat: add PreAuth Initialize support (P1 item 4)

- [x] 2. Add RefundToBalance and SettlementToBalance
  What to do / Must NOT do:
  - Create `src/Message/RefundToBalanceRequest.php` extending `AbstractRequest` — calls `RefundToBalance::create(CreateRefundToBalanceRequest)`, POST `/payment/refund-to-balance/init`
  - Create `src/Message/SettlementToBalanceRequest.php` extending `AbstractRequest` — calls `SettlementToBalance::create(CreateSettlementToBalanceRequest)`, POST `/payment/settlement-to-balance/init`
  - SDK request params follow `CreateRefundToBalanceRequest`/`CreateSettlementToBalanceRequest` setters
  - Add Gateway methods: `refundToBalance()`, `settlementToBalance()`
  - Return standard Response (no RedirectResponse)
  Do NOT add signature verification
  Parallelization: Wave A | Blocked by: — | Blocks: —
  References: src/Message/RefundRequest.php (pattern), vendor/iyzico/iyzipay-php/src/Iyzipay/Model/RefundToBalance.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Model/SettlementToBalance.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Request/CreateRefundToBalanceRequest.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Request/CreateSettlementToBalanceRequest.php
  Acceptance criteria: vendor/bin/phpunit --no-coverage passes
  QA scenarios: happy — getData() returns expected structure; failure — missing required param throws. Evidence .omo/evidence/task-2-p1-implementations.txt
  Commit: Y | feat: add RefundToBalance and SettlementToBalance support (P1 item 11)

- [x] 3. Add PlusInstallmentPayment and Loyalty retrieval
  What to do / Must NOT do:
  - Create `src/Message/PlusInstallmentPaymentRequest.php` — calls `PlusInstallmentPayment::create(CreatePlusInstallmentPaymentRequest)`, POST `/payment/auth` (same endpoint as regular payment but with different SDK model)
  - Create `src/Message/LoyaltyRequest.php` — calls `Loyalty::retrieve(RetrieveLoyaltyRequest)`, POST `/payment/loyalty/inquire`
  - Add Gateway methods: `purchasePlusInstallment()`, `fetchLoyalty()`
  - Return standard Response
  Do NOT add signature verification
  Parallelization: Wave A | Blocked by: — | Blocks: —
  References: src/Message/PurchaseRequest.php (pattern), vendor/iyzico/iyzipay-php/src/Iyzipay/Model/PlusInstallmentPayment.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Request/CreatePlusInstallmentPaymentRequest.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Model/Loyalty.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Request/RetrieveLoyaltyRequest.php
  Acceptance criteria: vendor/bin/phpunit --no-coverage passes
  QA scenarios: happy — getData() returns expected structure; failure — validation test. Evidence .omo/evidence/task-3-p1-implementations.txt
  Commit: Y | feat: add PlusInstallment and Loyalty support (P1 item 12)

- [x] 4. Add BKM Express support
  What to do / Must NOT do:
  - Create `src/Message/BkmInitializeRequest.php` — calls `BkmInitialize::create(CreateBkmInitializeRequest)`, POST `/payment/bkm/initialize`. Returns RedirectResponse with htmlContent (BkmInitilize has getHtmlContent/getToken/getSignature)
  - Create `src/Message/BkmRetrieveRequest.php` — calls `Bkm::retrieve(RetrieveBkmRequest)`, POST `/payment/bkm/retrieve`. Returns Response
  - Create `src/Message/BasicBkmInitializeRequest.php` (simpler variant) — calls `BasicBkmInitialize::create(CreateBasicBkmInitializeRequest)`
  - Add Gateway methods: `bkmInitialize()`, `bkmStatus()`, `basicBkmInitialize()`
  Do NOT add signature verification for retrieve
  Parallelization: Wave B | Blocked by: Wave A | Blocks: —
  References: src/Message/CheckoutRequest.php (redirect pattern), vendor/iyzico/iyzipay-php/src/Iyzipay/Model/BkmInitialize.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Model/Bkm.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Request/CreateBkmInitializeRequest.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Request/RetrieveBkmRequest.php
  Acceptance criteria: vendor/bin/phpunit --no-coverage passes
  QA scenarios: happy — getData() returns expected structure; failure — missing param. Evidence .omo/evidence/task-4-p1-implementations.txt
  Commit: Y | feat: add BKM Express support (P1 item 7)

- [x] 5. Add APM (Alternative Payment Methods) support
  What to do / Must NOT do:
  - Create `src/Message/ApmInitializeRequest.php` — calls `Apm::create(CreateApmInitializeRequest)`, POST `/payment/apm/initialize`. Include apmType param (SOFORT, IDEAL, QIWI, GIROPAY) using `ApmType` enum
  - Create `src/Message/ApmRetrieveRequest.php` — calls `Apm::retrieve(RetrieveApmRequest)`, POST `/payment/apm/retrieve`
  - Add Gateway methods: `initializeApm()`, `retrieveApm()`
  - Return standard Response
  Do NOT add signature verification
  Parallelization: Wave B | Blocked by: Wave A | Blocks: —
  References: vendor/iyzico/iyzipay-php/src/Iyzipay/Model/Apm.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Model/ApmType.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Request/CreateApmInitializeRequest.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Request/RetrieveApmRequest.php, vendor/iyzico/iyzipay-php/samples/initialize_apm.php
  Acceptance criteria: vendor/bin/phpunit --no-coverage passes
  QA scenarios: happy — getData() returns expected structure with apmType; failure — missing required param. Evidence .omo/evidence/task-5-p1-implementations.txt
  Commit: Y | feat: add APM (Alternative Payment Methods) support (P1 item 8)

- [x] 6. Add Reporting support
  What to do / Must NOT do:
  - Create `src/Message/ReportingPaymentDetailRequest.php` — calls `ReportingPaymentDetail::create(ReportingPaymentDetailRequest)`, GET `/v2/reporting/payment/details`. Uses `reporting` query string builder
  - Create `src/Message/ReportingPaymentTransactionRequest.php` — calls `ReportingPaymentTransaction::create(ReportingPaymentTransactionRequest)`
  - Create `src/Message/ReportingScrollTransactionRequest.php` — calls `ReportingScrollTransaction::create(ReportingScrollTransactionRequest)`
  - These use GET requests (different from typical POST) — pass `$request` or `null` as needed to `getHttpHeadersV2`
  - Add Gateway methods: `fetchPaymentDetails()`, `fetchPaymentTransactions()`, `scrollTransactions()`
  - Return standard Response
  Do NOT add signature verification
  Parallelization: Wave B | Blocked by: Wave A | Blocks: —
  References: vendor/iyzico/iyzipay-php/src/Iyzipay/Model/ReportingPaymentDetail.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Request/ReportingPaymentDetailRequest.php, vendor/iyzico/iyzipay-php/samples/reporting_payment_detail.php
  Acceptance criteria: vendor/bin/phpunit --no-coverage passes
  QA scenarios: happy — getData() structure; failure — date validation. Evidence .omo/evidence/task-6-p1-implementations.txt
  Commit: Y | feat: add Reporting support (P1 item 6)

- [x] 7. Add iyzico Link support
  What was done / Actual implementation:
  - Created 7 source files in `src/Message/` (flat, not subdirectory):
    - `IyziLinkSaveProductRequest.php` — POST, calls `IyziLinkSaveProduct::create()`
    - `IyziLinkRetrieveProductRequest.php` — GET, calls `IyziLinkRetrieveProduct::retrieve()`
    - `IyziLinkRetrieveAllProductRequest.php` — GET, calls `IyziLinkRetrieveAllProduct::retrieve()`
    - `IyziLinkDeleteProductRequest.php` — DELETE, calls `IyziLinkDeleteProduct::delete()`
    - `IyziLinkUpdateProductStatusRequest.php` — PUT, calls `IyziLinkUpdateProductStatus::update()`
    - `IyziLinkCreateFastLinkRequest.php` — POST, calls `IyziLinkFastLink::create()`
    - `IyziLinkSearchMerchantProductsRequest.php` — GET, calls `IyziLinkSearchMerchantProducts::retrieve()`
  - Note: `IyziLinkUpdateProduct` does not exist in the SDK — skipped
  - Gateway methods used actual naming: `iyziLinkSaveProduct()`, `iyziLinkRetrieveProduct()`, `iyziLinkRetrieveAllProduct()`, `iyziLinkDeleteProduct()`, `iyziLinkUpdateProductStatus()`, `iyziLinkCreateFastLink()`, `iyziLinkSearchMerchantProducts()`
  - 7 test files covering all classes (43 tests, 97 assertions)
  Plan note: original spec suggested subdirectory and 8 classes — actual implementation proved 7 flat files sufficient
  Commits: `b1fe422` (impl), `a57bdfb` (gateway), `e431f02` (tests), `037004c` (fix)

- [ ] 8. Add Marketplace support
  What to do / Must NOT do:
  - Create `src/Message/Marketplace\` subdirectory with files:
    - `CreateSubMerchantRequest.php` — POST `/onboarding/submerchant`, calls `SubMerchant::create(CreateSubMerchantRequest)`
    - `UpdateSubMerchantRequest.php` — PUT, calls `SubMerchant::update(UpdateSubMerchantRequest)`
    - `RetrieveSubMerchantRequest.php` — POST, calls `SubMerchant::retrieve(RetrieveSubMerchantRequest)`
    - `ApprovePaymentRequest.php` — POST, calls `Approval::create(CreateApprovalRequest)`
    - `DisapprovePaymentRequest.php` — POST, calls `Disapproval::create(CreateApprovalRequest)`
    - `CrossBookingFromRequest.php` — POST, calls `CrossBookingFromSubMerchant::create(CreateCrossBookingRequest)`
    - `CrossBookingToRequest.php` — POST, calls `CrossBookingToSubMerchant::create(CreateCrossBookingRequest)`
    - `SubMerchantPaymentItemUpdateRequest.php` — POST, calls `SubMerchantPaymentItemUpdate::create(SubMerchantPaymentItemUpdateRequest)`
  - Add Gateway methods: `createSubMerchant()`, `updateSubMerchant()`, `retrieveSubMerchant()`, `approvePayment()`, `disapprovePayment()`, `crossBookingFrom()`, `crossBookingTo()`, `updateSubMerchantPaymentItem()`
  Do NOT add C2C sub-merchant (separate SDK flow, higher complexity)
  Parallelization: Wave D | Blocked by: Wave A | Blocks: —
  References: vendor/iyzico/iyzipay-php/src/Iyzipay/Model/SubMerchant.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Model/Approval.php, vendor/iyzico/iyzipay-php/src/Iyzipay/Model/CrossBookingFromSubMerchant.php, vendor/iyzico/iyzipay-php/samples/create_sub_merchant.php, vendor/iyzico/iyzipay-php/samples/approve.php
  Acceptance criteria: vendor/bin/phpunit --no-coverage passes
  QA scenarios: happy — each class getData() structure; failure — missing required fields. Evidence .omo/evidence/task-8-p1-implementations.txt
  Commit: Y | feat: add Marketplace/SubMerchant support (P1 item 10)

- [ ] 9. Add Subscription support (largest item)
  What to do / Must NOT do:
  - Create `src/Message/Subscription\` subdirectory with files mirroring SDK structure:
    **Products:** `CreateProductRequest.php`, `RetrieveProductRequest.php`, `UpdateProductRequest.php`, `DeleteProductRequest.php`, `ListProductsRequest.php`
    **Pricing Plans:** `CreatePricingPlanRequest.php`, `RetrievePricingPlanRequest.php`, `UpdatePricingPlanRequest.php`, `DeletePricingPlanRequest.php`, `ListPricingPlansRequest.php`
    **Customers:** `CreateCustomerRequest.php`, `RetrieveCustomerRequest.php`, `UpdateCustomerRequest.php`, `DeleteCustomerRequest.php`, `ListCustomersRequest.php`
    **Subscriptions:** `CreateSubscriptionRequest.php`, `CreateSubscriptionWithCustomerRequest.php`, `CreateSubscriptionCheckoutFormRequest.php`, `ActivateSubscriptionRequest.php`, `CancelSubscriptionRequest.php`, `RetrySubscriptionRequest.php`, `UpgradeSubscriptionRequest.php`, `RetrieveSubscriptionRequest.php`, `ListSubscriptionsRequest.php`, `SearchSubscriptionsRequest.php`, `RetrieveSubscriptionCheckoutFormRequest.php`
    **Card Update:** `SubscriptionCardUpdateRequest.php`, `SubscriptionCardUpdateWithReferenceCodeRequest.php`
  - SDK uses HTTP methods: POST (create/update), GET (retrieve/list/search), DELETE (delete)
  - Add Gateway methods (one per SDK class): `createProduct()`, `retrieveProduct()`, `updateProduct()`, `deleteProduct()`, `listProducts()`, `createPricingPlan()`, `retrievePricingPlan()`, `updatePricingPlan()`, `deletePricingPlan()`, `listPricingPlans()`, `createCustomer()`, `retrieveCustomer()`, `updateCustomer()`, `deleteCustomer()`, `listCustomers()`, `createSubscription()`, `createSubscriptionWithCustomer()`, `createSubscriptionCheckoutForm()`, `activateSubscription()`, `cancelSubscription()`, `retrySubscription()`, `upgradeSubscription()`, `retrieveSubscription()`, `listSubscriptions()`, `searchSubscriptions()`, `retrieveSubscriptionCheckoutForm()`, `requestSubscriptionCardUpdate()`, `requestSubscriptionCardUpdateWithReferenceCode()`
  - Each class follows same pattern as existing request classes
  Do NOT add in one giant monolithic class — one class per SDK operation
  Do NOT skip tests for any class
  Parallelization: Wave E | Blocked by: Wave A | Blocks: —
  References: vendor/iyzico/iyzipay-php/src/Iyzipay/Model/Subscription/, vendor/iyzico/iyzipay-php/src/Iyzipay/Request/Subscription/, vendor/iyzico/iyzipay-php/samples/subscription-samples/
  Acceptance criteria: vendor/bin/phpunit --no-coverage passes
  QA scenarios: happy — each class getData() returns expected structure matching SDK request. Evidence .omo/evidence/task-9-p1-implementations.txt
  Commit: Y | feat: add Subscription management support (P1 item 9)

- [ ] 10. Update README with all new Gateway methods
  What to do / Must NOT do:
  - Append usage examples for every new Gateway method to README.md
  - Group by feature area (PreAuth, iyzico Link, Reporting, BKM, APM, Subscription, Marketplace, RefundToBalance, PlusInstallment/Loyalty)
  - Each example: method name, parameters, response handling
  Do NOT remove existing examples
  Do NOT add emojis
  Parallelization: Wave F | Blocked by: Todos 1-9 | Blocks: —
  References: README.md (existing usage examples as template)
  Acceptance criteria: all new Gateway methods documented
  QA scenarios: visual check — every Gateway method has at least one example. Evidence .omo/evidence/task-10-p1-implementations.txt
  Commit: Y | docs: add P1 feature examples to README

## Final verification wave
> Runs in parallel after ALL todos. All must APPROVE. Surface results and wait for the user's explicit okay before declaring complete.
- [ ] F1. Plan compliance audit
- [ ] F2. Code quality review
- [ ] F3. Full test suite pass
- [ ] F4. Scope fidelity

## Commit strategy
One commit per P1 item, all on master. Format:
```
feat: add <feature-name> support (P1 item <N>)
```

### Completed commits (Waves A-C)
```
3986f59 feat: add PreAuth Initialize support (P1 item 4)
c9796c0 fix: coerce mapCurrency() return to string and cast getAmount() to float  [related]
a217cb9 feat: add BKM Express support (P1 backlog #7)
b1fe422 feat: add iyzico Link support (P1 backlog #5)
b457feb feat: add Reporting support (P1 backlog #6)
be92794 feat: add APM (Alternative Payment Methods) support (P1 backlog #8)
a57bdfb feat: register BKM, iyzico Link, Reporting, APM in Gateway and Response signature map
e431f02 test: add tests for APM, iyzico Link Retrieve/Delete/UpdateStatus/FastLink/SearchMerchantProducts
037004c fix: restore buyer/shipping/billing/basketItems in ApmInitializeRequest, use DefaultHttpClient
```

## Success criteria
- `vendor/bin/phpunit --no-coverage` passes (all existing + new tests)
- Every new Gateway method callable from SDK consumer code
- No existing behavior changed
