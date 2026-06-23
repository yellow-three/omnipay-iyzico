# Draft: P1 Feature Implementations

## Status
- **Phase:** Awaiting approval
- **Next action:** User approves → execute via worker
- **Approach:** Multi-phase implementation of all P1 backlog items
- **Plan file:** `.omo/plans/p1-implementations.md`

## Research Findings

### Pattern (from existing code)
Every request class in `src/Message/`:
1. Extends `AbstractRequest`
2. Implements `getData(): array` (params from ParameterBag)
3. Implements `sendData($data)` (build SDK request, call SDK static method, wrap in `Response`)
4. Adds getters/setters for feature-specific params
5. Gateway method: `function operation(array $params) { return $this->createRequest(OperationRequest::class, $params); }`
6. Tests: success case + validation failure per class

### SDK Inventory (P1-relevant)

**Item 4 - PreAuth Initialize:**
- `CheckoutFormInitializePreAuth::create(CreateCheckoutFormInitializeRequest)` → `/payment/iyzipos/checkoutform/initialize/preauth/ecom`
- `BasicThreedsInitializePreAuth::create(CreatePaymentRequest)` → `/payment/3dsecure/initialize/preauth`
- Uses same `CreateCheckoutFormInitializeRequest` as CheckoutRequest

**Item 5 - iyzico Link:**
- `IyziLinkSaveProduct::create(IyziLinkSaveProductRequest)` → POST `/v2/iyzilink/products/`
- `IyziLinkRetrieveProduct::retrieve(IyziLinkRetrieveAllProduct)` → GET
- `IyziLinkRetrieveAllProduct::retrieve()` → GET
- `IyziLinkDeleteProduct::delete()` → DELETE
- `IyziLinkUpdateProduct::update(IyziLinkUpdateProduct)` → PUT
- `IyziLinkUpdateProductStatus::update(IyziLinkUpdateProductStatus)` → PUT
- `IyziLinkFastLink::create(IyziLinkFastLink)` → POST
- `IyziLinkSearchMerchantProducts::retrieve()` → GET

**Item 6 - Reporting:**
- `ReportingPaymentDetail::create(ReportingPaymentDetailRequest)` → GET `/v2/reporting/payment/details`
- `ReportingPaymentTransaction::create(ReportingPaymentTransactionRequest)` → GET `/v2/reporting/payment/transactions`
- `ReportingScrollTransaction::create(ReportingScrollTransactionRequest)` → GET

**Item 7 - BKM Express:**
- `BkmInitialize::create(CreateBkmInitializeRequest)` → POST `/payment/bkm/initialize`
- `Bkm::retrieve(RetrieveBkmRequest)` → POST `/payment/bkm/retrieve`
- `BasicBkmInitialize::create(CreateBasicBkmInitializeRequest)` → POST
- Note: `BkmInitialize` returns `htmlContent`, `token`, `signature` (like 3DS)

**Item 8 - APM:**
- `Apm::create(CreateApmInitializeRequest)` → POST `/payment/apm/initialize`
- `Apm::retrieve(RetrieveApmRequest)` → POST `/payment/apm/retrieve`

**Item 9 - Subscription (big):**
- Products CRUD: SubscriptionProduct (create/retrieve/update/delete)
- Pricing Plans CRUD: SubscriptionPricingPlan (create/retrieve/update/delete/list)
- Customers CRUD: SubscriptionCustomer (create/retrieve/update/delete/list)
- Subscription lifecycle: SubscriptionCreate, SubscriptionCreateWithCustomer, SubscriptionCreateCheckoutForm, SubscriptionActivate, SubscriptionCancel, SubscriptionRetry, SubscriptionUpgrade, SubscriptionDetails, SubscriptionList, RetrieveList, RetrieveSubscriptionCheckoutForm, SubscriptionCardUpdate
- All HTTP methods used (GET, POST, DELETE)

**Item 10 - Marketplace:**
- `SubMerchant::create/update/retrieve(CreateSubMerchantRequest/UpdateSubMerchantRequest/RetrieveSubMerchantRequest)` → `/onboarding/submerchant`
- `Approval::create(CreateApprovalRequest)` → POST
- `Disapproval::create(CreateApprovalRequest)` → POST
- `CrossBookingFromSubMerchant::create(CreateCrossBookingRequest)` → POST
- `CrossBookingToSubMerchant::create(CreateCrossBookingRequest)` → POST
- `SubMerchantPaymentItemUpdate::create(SubMerchantPaymentItemUpdateRequest)` → POST
- `C2CSubMerchant::create(CreateC2CSubMerchantRequest)` → POST

**Item 11 - RefundToBalance:**
- `RefundToBalance::create(CreateRefundToBalanceRequest)` → POST `/payment/refund-to-balance/init`
- `SettlementToBalance::create(CreateSettlementToBalanceRequest)` → POST `/payment/settlement-to-balance/init`

**Item 12 - PlusInstallment/Loyalty:**
- `PlusInstallmentPayment::create(CreatePlusInstallmentPaymentRequest)` → POST `/payment/auth`
- `Loyalty::retrieve(RetrieveLoyaltyRequest)` → POST `/payment/loyalty/inquire`
- Reward is a data model, not an API operation

### Key Decisions
1. **Separate Message classes per SDK model**, not overloaded existing classes (cleaner separation, follows existing pattern)
2. **Subscription gets its own subdirectory**: `src/Message/Subscription/` mirroring the SDK structure
3. **Some features need new Response subclasses** for specific data extraction (Iyzilink responses, Reporting responses)
4. **Gateway methods use Omnipay naming conventions:** camelCase, `fetch*` for queries, `create*` for mutations, `delete*` for deletions
5. **No signature verification for new GET endpoints** (matching existing pattern for read-only queries)
6. **All new classes get tests** — at minimum: data structure test + validation test per class
7. **README updated** at the end with all new Gateway method examples

### Effort Estimates
| Phase | Items | Classes | Est. Effort |
|-------|-------|---------|-------------|
| Phase 1 | 4, 11, 12 | ~8 Message + ~2 Response | 1 session |
| Phase 2 | 7, 8, 6 | ~7 Message + ~3 Response | 1-2 sessions |
| Phase 3 | 5, 10 | ~15 Message + ~5 Response | 2-3 sessions |
| Phase 4 | 9 (Subscription) | ~16 Message + ~4 Response | 2-3 sessions |
