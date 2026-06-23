# p1-implementations - Work Plan

## TL;DR (For humans)

**What you'll get:** Nine new feature groups added to the omnipay-iyzico gateway: PreAuth checkout forms, iyzico payment links, payment reporting, BKM Express, APM (alternative payment methods), full subscription management, marketplace/sub-merchant management, refund-to-balance, and installment/loyalty queries. Each comes with Gateway methods, request classes, response classes, and PHPUnit tests.

**Why this approach:** All P1 items follow the exact same pattern as the 17 existing Message classes (extend AbstractRequest, implement getData/sendData, call SDK static method). Grouping by complexity (simple → medium → large → subscription) reduces risk — earlier phases validate the pattern before the big items. Subscription gets its own subdirectory because it has ~16 SDK operations (CRUD on products/pricing-plans/customers + lifecycle).

**What it will NOT do:** Touch existing P0/P2/P3 backlog items. Not modify existing Message class behavior. Not add signature verification for new GET endpoints (matching existing pattern for read-only queries).

**Effort:** XL (~50 new Message classes, ~50 test files, 40+ Gateway methods)
**Risk:** Medium — pattern is well-established (17 existing request classes follow the exact same template), but volume is high
**Decisions to sanity-check:** Subscription scope (is full CRUD needed or just create/retrieve?), Marketplace scope (which sub-merchant types?), Gateway method naming for link/product operations

Your next move: approve this plan. Full execution detail follows below.

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
### Parallel execution waves
- Wave A: Items 4, 11, 12 (simplest, independent)
- Wave B: Items 7, 8, 6 (medium, independent)
- Wave C: Item 5 (iyzico Link — multi-method)
- Wave D: Item 10 (Marketplace — multi-method)
- Wave E: Item 9 (Subscription — largest)
- Wave F: README update + final verification

### Dependency matrix
| Todo | Depends on | Blocks | Can parallelize with |
| --- | --- | --- | --- |
| All within a Wave | — | — | All others in same Wave |
| Wave B | Wave A (pattern confidence) | — | — |
| Wave C | Wave A | — | — |
| Wave D | Wave A | — | — |
| Wave E | Wave A | — | — |
| Wave F (README) | Waves A-E | — | — |
| Verification | All | — | — |

## Todos
> Implementation + Test = ONE todo. Never separate.
<!-- APPEND TASK BATCHES BELOW THIS LINE WITH edit/apply_patch - never rewrite the headers above. -->
- [ ] 1. Add PreAuth Initialize: CheckoutFormPreAuth, PayWithIyzicoPreAuth, BasicThreedsPreAuth
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

- [ ] 2. Add RefundToBalance and SettlementToBalance
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

- [ ] 3. Add PlusInstallmentPayment and Loyalty retrieval
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

- [ ] 4. Add BKM Express support
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

- [ ] 5. Add APM (Alternative Payment Methods) support
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

- [ ] 6. Add Reporting support
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

- [ ] 7. Add iyzico Link support
  What to do / Must NOT do:
  - Create `src/Message/Iyzilink\` subdirectory with files:
    - `IyziLinkSaveProductRequest.php` — POST `/v2/iyzilink/products/`, calls `IyziLinkSaveProduct::create(IyziLinkSaveProductRequest)`
    - `IyziLinkRetrieveProductRequest.php` — GET, calls `IyziLinkRetrieveProduct::retrieve()`
    - `IyziLinkListProductsRequest.php` — GET, calls `IyziLinkRetrieveAllProduct::retrieve()`
    - `IyziLinkDeleteProductRequest.php` — DELETE, calls `IyziLinkDeleteProduct::delete()`
    - `IyziLinkUpdateProductRequest.php` — PUT, calls `IyziLinkUpdateProduct::update()`
    - `IyziLinkUpdateProductStatusRequest.php` — PUT, calls `IyziLinkUpdateProductStatus::update()`
    - `IyziLinkCreateFastLinkRequest.php` — POST, calls `IyziLinkFastLink::create()`
    - `IyziLinkSearchMerchantProductsRequest.php` — GET, calls `IyziLinkSearchMerchantProducts::retrieve()`
  - Add Gateway methods: `createPaymentLink()`, `retrievePaymentLink()`, `listPaymentLinks()`, `deletePaymentLink()`, `updatePaymentLink()`, `updatePaymentLinkStatus()`, `createFastLink()`, `searchMerchantProducts()`
  - These use mixed HTTP methods (GET, POST, PUT, DELETE)
  Do NOT add signature verification
  Parallelization: Wave C | Blocked by: Wave A | Blocks: —
  References: vendor/iyzico/iyzipay-php/src/Iyzipay/Model/Iyzilink/, vendor/iyzico/iyzipay-php/src/Iyzipay/Request/Iyzilink/, vendor/iyzico/iyzipay-php/samples/iyzilink_*.php
  Acceptance criteria: vendor/bin/phpunit --no-coverage passes
  QA scenarios: happy — each class getData() structure; failure — validation. Evidence .omo/evidence/task-7-p1-implementations.txt
  Commit: Y | feat: add iyzico Link support (P1 item 5)

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
One commit per P1 item (9 commits), all on master. Format:
```
feat: add <feature-name> support (P1 item <N>)
```

## Success criteria
- `vendor/bin/phpunit --no-coverage` passes (all existing + new tests)
- Every new Gateway method callable from SDK consumer code
- No existing behavior changed
