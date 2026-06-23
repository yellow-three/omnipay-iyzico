# readme-examples-update - Work Plan

## TL;DR (For humans)

**What you'll get:** Updated README with CompletePurchase example, expanded AcceptNotification/webhook documentation with HMAC signature details, Response methods table, End-to-End 3DS flow, and a new `examples/` directory with 6 reference guides covering every feature.

**What it will NOT do:** Change any source code or tests. Docs only.

**Effort:** Medium (2 files: README.md + 6 example files)
**Risk:** None

---

## Scope

### Must have
1. README: Add **CompletePurchase (3DS callback)** example with code block
2. README: Expand **AcceptNotification/Webhook** section with HMAC-SHA256 `isValid()`, Direct vs HPP format detection, `getTransactionReference()` fallback chain
3. README: Add **Response Methods** table listing all public getters (isSuccessful, getPaymentId, getCardType, getBankName, getCheckoutFormContent, etc.)
4. README: Add **End-to-End 3DS Flow** walkthrough (initialize → purchase → callback → completePurchase)
5. `examples/` directory with reference markdown files:
   - `01-basic-payment.md`
   - `02-3ds-flow.md`
   - `03-card-storage.md`
   - `04-bin-installment.md`
   - `05-pwi-checkout.md`
   - `06-webhook.md`

### Must NOT have
- No changes to src/ or tests/ files
- No runnable PHP files (reference .md only)
- No real API keys or secrets

---

## Execution

Delegated to a single task: update README.md and create examples/*.md files.

### README güncellemeleri:
1. **"Complete Purchase (3DS Callback)"** bölümü ekle — Checkout Status ile Bin Number arasına, POST callback notuyla
2. **"Webhook / Accept Notification"** bölümünü genişlet — HMAC doğrulama, isValid(), Direct vs HPP format, getTransactionReference() fallback zinciri
3. **"Response Methods"** tablosu ekle — tüm public getter'lar (isSuccessful, isPending, getStatus, getPaymentStatus, getConversationId, getToken, getTransactionReference, getTransactionId, getMessage, getCode, getPaymentId, getPaidPrice, getCardType, getCardAssociation, getCardFamily, getCardToken, getCardUserKey, getBinNumber, getLastFourDigits, getAuthCode, getConnectorName, getPaymentTransactionId, getTokenExpireTime, getPaymentPageUrl, getCheckoutFormContent, getHtmlContent, getMdStatus, getCallbackUrl, getSignature, getBankName, getBankCode, getCommercial, getInstallmentDetails, getExternalId, getCardAlias, getCardBankCode, getCardBankName, getCardDetails, getPayWithIyzicoPageUrl, getPayWithIyzicoContent)
4. **"End-to-End 3DS Flow"** bölümü ekle — tüm akışı göster
5. **"Important Notes"** altına webhook notları ekle (signature zorunluluğu, Direct vs HPP farkı)

### Examples klasörü:
- `examples/` dizinini oluştur
- Her biri başlık + giriş + kod bloğu + açıklama formatında 6 .md dosyası

---

## Verification
- Dosyaların varlığını kontrol et
- README'de link varsa çalıştığını kontrol et
- Full test suite çalıştır (dokümantasyon değişikliği etkilememeli)
