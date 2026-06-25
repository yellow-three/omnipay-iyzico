# omnipay-iyzico — Öncelik Sıralı Backlog

## Bağlam

Repo: https://github.com/yellow-three/omnipay-iyzico
Referans SDK: https://github.com/iyzico/iyzipay-php (resmi PHP SDK)
Resmi dokümantasyon indeksi: https://docs.iyzico.com/llms.txt

Bu liste, omnipay-iyzico'nun mevcut kodu ile resmi `iyzico/iyzipay-php` SDK'sının
`Model/` ve `Request/` klasörlerindeki TÜM sınıflar karşılaştırılarak, ayrıca
docs.iyzico.com üzerindeki ilgili sayfalar tek tek okunarak çıkarılmıştır.
Genel (platform-bağımsız) bir Omnipay sürücüsü hedefleniyor — Bagisto'ya özel
bir öncelik sıralaması DEĞİLDİR.

Her madde bağımsız bir iş birimi olarak ele alınabilir; P0'dan başlanması
önerilir. Her madde için: önce ilgili docs.iyzico.com sayfasını/SDK dosyasını
kontrol et, varsayımla gitme.

---

## P0 — Güvenlik / Parasal Risk (önce bunlar yapılmalı)

### 1. Response Signature Validation eksik

Kaynak: https://docs.iyzico.com/en/advanced/response-signature-validation.md

Webhook imza doğrulamasından (AcceptNotificationRequest, zaten yapıldı) TAMAMEN
AYRI bir mekanizma: her senkron API yanıtı kendi `signature` alanını taşıyor ve
doğrulanması gerekiyor. Şu an Response.php/RedirectResponse.php'de buna dair
hiçbir şey yok.

**Algoritma:** `HMAC-SHA256(secretKey, params.join(":"))`, hex çıktı.
Webhook'unkinden farklı: ayraç `":"` var, ham concat değil.

**Endpoint'e göre parametre sırası (resmi dokümandan):**

| Servis | Endpoint | Parametre sırası |
|---|---|---|
| Non-3DS | `/payment/auth` | paymentId, currency, basketId, conversationId, paidPrice, price |
| Non-3DS PreAuth | `/payment/preauth` | paymentId, currency, basketId, conversationId, paidPrice, price |
| Non-3DS PostAuth | `/payment/postauth` | paymentId, currency, basketId, conversationId, paidPrice, price |
| Retrieve Payment | `/payment/detail` | paymentId, currency, basketId, conversationId, paidPrice, price |
| 3DS Initialize | `/payment/3dsecure/initialize` | paymentId, conversationId |
| 3DS PreAuth Initialize | `/payment/3dsecure/initialize/preauth` | paymentId, conversationId |
| 3DS Auth | `/payment/3dsecure/auth` | paymentId, currency, basketId, conversationId, paidPrice, price |
| 3DS v2 Auth | `/payment/v2/3dsecure/auth` | paymentId, currency, basketId, conversationId, paidPrice, price |
| callbackURL yönlendirmesi | — | conversationData, conversationId, mdStatus, paymentId, status |
| CF Initialize | `/payment/iyzipos/checkoutform/initialize/auth/ecom` | conversationId, token |
| PWI Initialize | `/payment/pay-with-iyzico/initialize` | conversationId, token |
| CF PreAuth Initialize | `/payment/iyzipos/checkoutform/initialize/preauth/ecom` | conversationId, token |
| CF Retrieve | `/payment/iyzipos/checkoutform/auth/ecom/detail` | paymentStatus, paymentId, currency, basketId, conversationId, paidPrice, price, token |
| Refund | `/payment/refund` | paymentId, price, currency, conversationId |
| Amount Base Refund | `/v2/payment/refund` | paymentId, price, currency, conversationId |

**KRİTİK — "Trailing Zero" normalizasyonu:** Hesaplamadan ÖNCE her `price`/
`paidPrice` değerinden sondaki sıfırlar atılmalı:
- `"50.00"` → `"50"`
- `"10.0"` → `"10"`
- `"10.50"` → `"10.5"`
- `"10.510"` → `"10.51"`
Bu adım atlanırsa imza her zaman yanlış çıkar. iyzico bu fonksiyonu SDK'da
sağlamıyor, merchant'ın kendisi implemente etmesi gerekiyor.

**Yapılması gereken:**
- Response.php'ye (veya ayrı bir trait/helper sınıfa) endpoint-bağımsız bir
  `verifySignature(string $secretKey, array $orderedFields): bool` metodu ekle.
- Her Message sınıfının `sendData()`'sında, hangi response geldiyse o
  endpoint'in doğru alan sırasını vererek bu metodu çağırabilmesi için bir yol
  kur (örn. her Response alt sınıfı kendi `getSignatureFields(): array`'ini
  döndürsün).
- `hash_equals()` kullan (timing-safe), `==`/`===` kullanma.
- Testler: gerçek dokümandaki örnek değerlerle (secretKey, paymentId, currency,
  basketId, conversationId, paidPrice, price → beklenen signature) en az
  Non-3DS için bağımsız bir test yaz.

### 2. mapCurrency() desteklenmeyen para birimini sessizce TRY'ye çeviriyor

Dosya: `src/Message/AbstractRequest.php`, `mapCurrency()` metodu.

Şu an:
```php
protected function mapCurrency(string $currency): string
{
    return match (strtoupper($currency)) {
        'TRY', 'TL' => IyzicoCurrency::TL,
        'USD' => IyzicoCurrency::USD,
        'EUR' => IyzicoCurrency::EUR,
        'GBP' => IyzicoCurrency::GBP,
        'RUB' => IyzicoCurrency::RUB,
        'IRR' => IyzicoCurrency::IRR,
        'NOK' => IyzicoCurrency::NOK,
        'CHF' => IyzicoCurrency::CHF,
        default => IyzicoCurrency::TL,   // <-- SORUN
    };
}
```
(Not: desteklenen para birimi listesi doğru ve `Iyzipay\Model\Currency`'nin tüm
sabitlerini kapsıyor — bu kısma dokunma. Sorun sadece `default` davranışı.)

Bilinmeyen bir para birimi gelirse (örn. JPY, CAD, SEK) sessizce TRY'ye
düşülüyor — müşteriden yanlış tutarda/yanlış para biriminde tahsilat riski.

**Yapılması gereken:** `default` dalında
`Omnipay\Common\Exception\InvalidRequestException` fırlat (paket zaten bu
sınıfı kullanıyor, `PurchaseRequest.php`'de import edilmiş — aynı deseni
kullan). Mesaj: desteklenen para birimi listesini içersin.

### 3. RefundRequest'te varsayılan 'reason' değeri yanlış

Dosya: `src/Message/RefundRequest.php`

```php
'reason' => $this->getParameter('reason') ?? 'buyer request',  // <-- boşluklu, yanlış
```

Gerçek SDK enum'u `Iyzipay\Model\RefundReason::BUYER_REQUEST` değeri
`"buyer_request"` (alt çizgili). Boşluklu hali API tarafında tanınmayabilir.

**Yapılması gereken:** `Iyzipay\Model\RefundReason::BUYER_REQUEST` sabitini
import edip varsayılan olarak kullan (literal string yazmak yerine sabiti
kullanmak gelecekte SDK değerleri değişirse otomatik senkron kalmasını sağlar).

---

## P1 — SDK'da TAM destek var, eklemek orta efor

Bu maddelerin hepsi için resmi `iyzico/iyzipay-php`'de gerekli Model + Request
sınıfları zaten mevcut — yeni bir SDK metodu yazmaya gerek yok, sadece
omnipay-iyzico'da bunlara karşılık gelen Gateway metodu + Message sınıfı
eklenmesi gerekiyor (mevcut Message sınıflarındaki desene sadık kal: getData()
+ sendData() + ilgili get/set parametre metotları).

### 4. Checkout Form / PWI PreAuth Initialize
SDK sınıfları: `Iyzipay\Model\CheckoutFormInitializePreAuth`,
`Iyzipay\Model\BasicThreedsInitializePreAuth`.
Mevcut `CheckoutRequest`/`PayWithIyzicoInitializeRequest`'in yanına, ön
provizyon (preauth) modunda başlatma yapan kardeş sınıflar veya mevcut
sınıflara bir `preAuth: bool` parametresi eklenebilir.

### 5. iyzico Link
SDK sınıfları (hepsi `Iyzipay\Model\Iyzilink\` ve `Iyzipay\Request\Iyzilink\`
altında): `IyziLinkSaveProduct`, `IyziLinkRetrieveProduct`,
`IyziLinkRetrieveAllProduct`, `IyziLinkDeleteProduct`,
`IyziLinkUpdateProductStatus`, `IyziLinkCreateFastLink`,
`IyziLinkSearchMerchantProducts`.
Gateway'e karşılık gelen metotlar: `createPaymentLink`, `retrievePaymentLink`,
`listPaymentLinks`, `deletePaymentLink`, `updatePaymentLinkStatus`,
`createFastLink`, `searchMerchantProducts` (isimlendirmeyi mevcut paket
konvansiyonuna uyarlayabilirsin).

### 6. Reporting
SDK sınıfları: `Iyzipay\Model\ReportingPaymentDetail`,
`ReportingPaymentTransaction`, `ReportingScrollTransaction` (+ ilgili Request
sınıfları `ReportingPaymentDetailRequest`, `ReportingPaymentTransactionRequest`,
`ReportingScrollTransactionRequest`).

### 7. BKM Express
Türkiye'de yaygın bir ödeme yöntemi. SDK sınıfları: `Iyzipay\Model\Bkm`,
`BkmInitialize`, `BasicBkmInitialize`, `BkmInstallment`,
`BkmInstallmentPrice` (+ `CreateBkmInitializeRequest`,
`CreateBasicBkmInitializeRequest`, `RetrieveBkmRequest`).

### 8. APM (Alternative Payment Methods)
Avrupa cüzdan/yöntemleri: SOFORT, IDEAL, QIWI, GIROPAY. SDK sınıfları:
`Iyzipay\Model\Apm`, `ApmType` (enum: SOFORT, IDEAL, QIWI, GIROPAY), `ApmResource`
(+ `CreateApmInitializeRequest`, `RetrieveApmRequest`).

### 9. Subscription (Abonelik)
SDK'da çok kapsamlı bir alt sistem — `Iyzipay\Model\Subscription\` ve
`Iyzipay\Request\Subscription\` altında ~15 sınıf: Product (create/update/
delete/retrieve/list), PricingPlan (create/update/delete/retrieve/list),
Customer (create/update/delete/retrieve/list, with-customer create),
Subscription (create/activate/cancel/retry/upgrade/list/search/details),
CardUpdate (+ reference-code varyantı), CheckoutForm tabanlı abonelik
başlatma + retrieve.
Bu, tek başına büyük bir iş paketi — ayrı bir alt-görev olarak ele alınabilir,
mevcut tek-seferlik ödeme Message desenine benzer şekilde her biri için ayrı
Message sınıfları gerekir.

### 10. Marketplace (Pazaryeri)
SDK sınıfları: `Iyzipay\Model\SubMerchant` (create/update/retrieve),
`Approval`, `Disapproval`, `CrossBookingFromSubMerchant`,
`CrossBookingToSubMerchant`, `C2CSubMerchant` (+ API credentials, SMS
verification encrypter varyantları), `SubMerchantPaymentItemUpdate`.

### 11. RefundToBalance / SettlementToBalance
Merchant bakiyesine iade/mahsup işlemleri. SDK sınıfları:
`Iyzipay\Model\RefundToBalance`, `RefundToBalanceResource`,
`SettlementToBalance`, `SettlementToBalanceResource`
(+ `CreateRefundToBalanceRequest`, `CreateSettlementToBalanceRequest`).

### 12. PlusInstallmentPayment / Loyalty / Reward
Taksit + sadakat puanı kampanyaları. SDK sınıfları:
`Iyzipay\Model\PlusInstallmentPayment`, `PlusInstallmentPaymentResource`,
`Loyalty`, `Reward` (+ `CreatePlusInstallmentPaymentRequest`,
`RetrieveLoyaltyRequest`).

---

## P2 — SDK'da SADECE KISMİ destek var

### 13. UCS / Tokenization Session Akışı
Doküman: https://docs.iyzico.com/en/payment-methods/tokenization/tokenization-integration.md

Dokümanda anlatılan adımlar: Access Token Retrieval, Initialize Payment with
Session (Balance Payment / Card Payment), Session Expire, Retrieve Session,
Last Payment Detail Info.

SDK'da SADECE şu var: `Iyzipay\Model\UCSInitialize::create()`
(`/v2/ucs/init` endpoint'i, `Iyzipay\Request\UCSInitializeRequest`).

Diğer adımların (session sorgulama, balance/card payment via session, session
sonlandırma, access token alma) SDK'da karşılığı bulunamadı. Bu adımları
implemente etmek istersen muhtemelen ham HTTP isteği (kendi auth/imza header
mantığınla, `Iyzipay\IyzipayResource`'un nasıl HTTP header/auth ürettiğine
bakarak) gerekecek — önce bunu tekrar doğrula, SDK yeni bir sürümde
güncellenmiş olabilir.

---

## P3 — Resmi PHP SDK'sında HİÇ karşılığı yok

Bu maddeler için `iyzico/iyzipay-php` paketinde aranan hiçbir sınıf
bulunamadı (Model/ ve Request/ klasörleri tamamen tarandı). Eklenmek
istenirse SDK üzerinden değil, ham HTTP isteği + kendi imza/auth header
mantığınızı (IYZWSv2 / HMACSHA256 auth, bkz.
https://docs.iyzico.com/en/getting-started/preliminaries/authentication.md)
yazarak yapılması gerekir — bu, P1'deki maddelere göre çok daha yüksek efor.
Başlamadan önce SDK'nın güncel bir sürümde bunları eklemiş olup olmadığını
tekrar kontrol et (composer.json'da hangi sürüm pinlenmiş, packagist'teki en
güncel sürüme bak).

### 14. Mass Payout (Toplu Ödeme)
Doküman: https://docs.iyzico.com/en/products/mass-payout.md
(Initialize, Auth, Cancel, Retrieve, Reporting, Bakiye Sorgulama)

### 15. Korumalı Havale/EFT (Bank Transfer ödeme yöntemi olarak)
Doküman: https://docs.iyzico.com/en/products/bank-transfer.md
NOT: SDK'da `Iyzipay\Model\BankTransfer.php` diye bir sınıf var ama bu sadece
Marketplace alt-üye IBAN/iletişim bilgisi tutan bir VERİ sınıfı —
gerçek "ödeme yöntemi olarak banka havalesi" akışıyla ilgisi yok, karıştırma.

### 16. Shopping Credit (Alışveriş Kredisi)
Doküman: https://docs.iyzico.com/en/products/shopping-credit.md

### 17. PayPos / Ceppos App2App, Physical POS / Terminal API
Mobil session-bazlı ödeme akışları ve fiziksel terminal cihaz entegrasyonu.
Muhtemelen tamamen ayrı SDK'lar/protokoller gerektiriyor, bu paketin genel
kapsamına girip girmeyeceği baştan tartışılmalı.

---

## Genel kabul kriterleri (her madde için geçerli)

- `php -l` ile syntax kontrolü.
- Eklenen her Message sınıfı için, gerçek SDK'daki ilgili sınıf/metot
  isimlerini VARSAYMADAN, `iyzico/iyzipay-php` kaynağından (GitHub'dan klonla,
  `grep -rn "public static function\|public function set"` ile) doğrula.
- Mevcut testler kırılmamalı.
- Her yeni Message sınıfı için en az: başarılı senaryo + validate() ile
  zorunlu alan kontrolü testi.
- README.md'ye yeni Gateway metotları için kısa kullanım örneği eklenmeli.
- Kapsam dışı: bu listede olmayan hiçbir şeye dokunma (örn. zaten doğrulanmış
  PurchaseRequest, CheckoutRequest, AcceptNotificationRequest, Card Storage,
  BinNumber/Installment, PWI — bunlar P0/P1/P2/P3 dışında ayrıca
  belirtilmedikçe kapsam dışıdır).
