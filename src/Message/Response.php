<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;

class Response extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * Fields read off an iyzico SDK model object via its getters.
     *
     * iyzico's model classes (Payment, Refund, ThreedsInitialize, ...) don't expose a
     * getRawResult()/toArray() method, so we can't reliably round-trip them through JSON.
     * Instead we pull the known fields directly via their own getters when present.
     */
    private const IYZICO_FIELDS = [
        'status', 'errorCode', 'errorMessage', 'errorGroup', 'locale', 'systemTime',
        'conversationId', 'paymentId', 'paymentStatus', 'price', 'paidPrice', 'currency',
        'installment', 'fraudStatus', 'basketId', 'cardType', 'cardAssociation', 'cardFamily',
        'cardToken', 'cardUserKey', 'binNumber', 'lastFourDigits', 'authCode', 'connectorName',
        'paymentTransactionId', 'token', 'tokenExpireTime', 'paymentPageUrl',
        'checkoutFormContent', 'htmlContent', 'mdStatus', 'callbackUrl', 'signature',
        'bankName', 'bankCode', 'commercial', 'installmentDetails',
        'externalId', 'cardAlias', 'cardBankCode', 'cardBankName', 'cardDetails',
        'payWithIyzicoPageUrl', 'payWithIyzicoContent',
        'rewardAmount', 'rewardUsage', 'commissionRate', 'commissionRateAmount', 'phase', 'posOrderId',
    ];

    public function __construct(RequestInterface $request, mixed $data)
    {
        $this->request = $request;
        $this->data = $this->normalizeData($data);
    }

    private function normalizeData(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_string($data)) {
            return json_decode($data, true) ?? [];
        }

        if (is_object($data)) {
            if (method_exists($data, 'getRawResult')) {
                $raw = $data->getRawResult();
                if (is_string($raw) && $raw !== '') {
                    $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    if (isset($decoded['threeDSHtmlContent']) && !isset($decoded['htmlContent'])) {
                        $decoded['htmlContent'] = base64_decode($decoded['threeDSHtmlContent']);
                        $decoded['_html_decoded'] = true;
                    } elseif (isset($decoded['htmlContent']) && !isset($decoded['_html_decoded'])) {
                        $decoded['htmlContent'] = base64_decode($decoded['htmlContent']);
                        $decoded['_html_decoded'] = true;
                    }
                    return $decoded;
                }
                }
            }

            $result = [];

            foreach (self::IYZICO_FIELDS as $field) {
                $getter = 'get'.ucfirst($field);

                if (method_exists($data, $getter)) {
                    $result[$field] = $data->{$getter}();
                }
            }

            return $result;
        }

        return [];
    }

    public function isSuccessful(): bool
    {
        return isset($this->data['status']) && $this->data['status'] === 'success';
    }

    public function isPending(): bool
    {
        return isset($this->data['status']) && $this->data['status'] === 'pending';
    }

    public function isRedirect(): bool
    {
        return false;
    }

    public function getStatus(): ?string
    {
        return $this->data['status'] ?? null;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->data['paymentStatus'] ?? null;
    }

    public function getConversationId(): ?string
    {
        return $this->data['conversationId'] ?? null;
    }

    public function getToken(): ?string
    {
        return $this->data['token'] ?? null;
    }

    public function getTransactionReference(): ?string
    {
        return $this->data['paymentId'] ?? $this->data['conversationId'] ?? null;
    }

    public function getTransactionId(): ?string
    {
        return $this->getPaymentTransactionId();
    }

    public function getMessage(): ?string
    {
        if (isset($this->data['errorMessage'])) {
            return $this->data['errorMessage'] . ' (errorCode: ' . ($this->data['errorCode'] ?? '') . ')';
        }

        return $this->data['message'] ?? null;
    }

    public function getCode(): ?string
    {
        return $this->data['errorCode'] ?? null;
    }

    public function getPaymentId(): ?string
    {
        return $this->data['paymentId'] ?? null;
    }

    public function getCardToken(): ?string
    {
        return $this->data['cardToken'] ?? null;
    }

    public function getCardUserKey(): ?string
    {
        return $this->data['cardUserKey'] ?? null;
    }

    /**
     * Per-basket-item breakdown of a payment. Each entry carries the
     * paymentTransactionId that refund() needs.
     */
    public function getItemTransactions(): array
    {
        $items = $this->data['itemTransactions'] ?? [];

        return is_array($items) ? $items : [];
    }

    /**
     * paymentTransactionId of every basket item, in basket order — pass one of these
     * as `paymentTransactionId` to refund().
     *
     * @return string[]
     */
    public function getPaymentTransactionIds(): array
    {
        $ids = [];

        foreach ($this->getItemTransactions() as $item) {
            if (is_array($item) && isset($item['paymentTransactionId'])) {
                $ids[] = (string) $item['paymentTransactionId'];
            }
        }

        return $ids;
    }

    /**
     * Read a scalar response field as a string (iyzico sends some of these, e.g.
     * binNumber or mdStatus, as JSON numbers).
     */
    private function stringField(string $field): ?string
    {
        $value = $this->data[$field] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }

    public function getCardType(): ?string
    {
        return $this->stringField('cardType');
    }

    public function getCardAssociation(): ?string
    {
        return $this->stringField('cardAssociation');
    }

    public function getCardFamily(): ?string
    {
        return $this->stringField('cardFamily');
    }

    public function getBinNumber(): ?string
    {
        return $this->stringField('binNumber');
    }

    public function getLastFourDigits(): ?string
    {
        return $this->stringField('lastFourDigits');
    }

    public function getAuthCode(): ?string
    {
        return $this->stringField('authCode');
    }

    public function getConnectorName(): ?string
    {
        return $this->stringField('connectorName');
    }

    public function getBankName(): ?string
    {
        return $this->stringField('bankName');
    }

    public function getBankCode(): ?string
    {
        return $this->stringField('bankCode');
    }

    public function getExternalId(): ?string
    {
        return $this->stringField('externalId');
    }

    public function getCardAlias(): ?string
    {
        return $this->stringField('cardAlias');
    }

    public function getCardBankCode(): ?string
    {
        return $this->stringField('cardBankCode');
    }

    public function getCardBankName(): ?string
    {
        return $this->stringField('cardBankName');
    }

    public function getSignature(): ?string
    {
        return $this->stringField('signature');
    }

    public function getMdStatus(): ?string
    {
        return $this->stringField('mdStatus');
    }

    public function getCallbackUrl(): ?string
    {
        return $this->stringField('callbackUrl');
    }

    public function getPaymentPageUrl(): ?string
    {
        return $this->stringField('paymentPageUrl');
    }

    public function getPayWithIyzicoPageUrl(): ?string
    {
        return $this->stringField('payWithIyzicoPageUrl');
    }

    public function getPayWithIyzicoContent(): ?string
    {
        return $this->stringField('payWithIyzicoContent');
    }

    public function getCommercial(): ?int
    {
        $value = $this->data['commercial'] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    public function getInstallmentDetails(): ?array
    {
        $value = $this->data['installmentDetails'] ?? null;

        return is_array($value) ? $value : null;
    }

    /**
     * paymentTransactionId of the first basket item (iyzico only reports it per item).
     * Use getPaymentTransactionIds() when the basket has more than one item.
     */
    public function getPaymentTransactionId(): ?string
    {
        return $this->stringField('paymentTransactionId') ?? ($this->getPaymentTransactionIds()[0] ?? null);
    }

    public function getPaidPrice(): ?string
    {
        return $this->data['paidPrice'] ?? null;
    }

    public function getCheckoutFormContent(): ?string
    {
        return $this->data['checkoutFormContent'] ?? null;
    }

    public function getRedirectUrl(): ?string
    {
        return null;
    }

    public function getRedirectMethod(): string
    {
        return 'POST';
    }

    public function getRedirectData(): array
    {
        return [];
    }

    /**
     * Remove trailing zeros from price strings for signature computation.
     * "50.00" → "50", "10.50" → "10.5", "10.510" → "10.51"
     */
    public static function normalizeTrailingZero(string $value): string
    {
        if (str_contains($value, '.')) {
            $value = rtrim(rtrim($value, '0'), '.');
        }
        return $value;
    }

    /**
     * Verify HMAC-SHA256 signature against ordered field values.
     *
     * @param string $secretKey The merchant secret key
     * @param array $fieldNames Ordered array of field names to include in signature
     * @param array $options Optional: ['priceFields' => ['price', 'paidPrice']] for normalization
     * @return bool True if signature matches
     */
    public function verifySignature(string $secretKey, array $fieldNames, array $options = []): bool
    {
        $signature = $this->data['signature'] ?? '';
        if (empty($signature)) {
            return false;
        }

        $priceFields = $options['priceFields'] ?? ['price', 'paidPrice'];
        // iyzico signs only the field values joined by ':' — the secret key is the
        // HMAC key, it is NOT part of the signed message (see iyzipay-php samples).
        $parts = [];

        foreach ($fieldNames as $field) {
            $value = $this->data[$field] ?? '';
            if (in_array($field, $priceFields, true)) {
                $value = self::normalizeTrailingZero($value);
            }
            $parts[] = $value;
        }

        $message = implode(':', $parts);
        $computed = hash_hmac('sha256', $message, $secretKey);

        return hash_equals($computed, $signature);
    }

    /**
     * Store the signature verification result.
     */
    public function setSignatureValid(?bool $valid): void
    {
        $this->data['_signature_valid'] = $valid;
    }

    /**
     * Returns whether the response signature was verified.
     *
     * @return bool|null null if no verification was performed
     */
    public function isSignatureValid(): ?bool
    {
        return $this->data['_signature_valid'] ?? null;
    }

    /**
     * Get the ordered field names for signature verification by endpoint key.
     *
     * Every entry mirrors the calculateHmacSHA256Signature() call in the matching
     * iyzipay-php sample (vendor/iyzico/iyzipay-php/samples), except "refund", which
     * follows iyzico's response-signature documentation. Endpoints iyzico does not sign
     * (cancel, card storage, BIN/installment lookup, marketplace, reporting, ...) are
     * deliberately absent: applySignature() then reports null ("not verified") instead
     * of a misleading false.
     *
     * The result is informational: it is exposed via isSignatureValid() and never
     * blocks processing on its own.
     *
     * @return array|null Ordered field names, or null if the endpoint is not signed/unknown
     */
    public static function getSignatureFieldOrder(string $endpoint): ?array
    {
        $payment = ['paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price'];
        $hostedInit = ['conversationId', 'token'];
        $hostedRetrieve = ['paymentStatus', 'paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price', 'token'];

        $map = [
            'non-3ds' => $payment,
            'preauth' => $payment,
            'postauth' => $payment,
            'payment-detail' => $payment,
            '3ds-auth' => $payment,
            '3ds-v2-auth' => $payment,
            '3ds-init' => ['paymentId', 'conversationId'],
            '3ds-preauth-init' => ['paymentId', 'conversationId'],
            'checkout-init' => $hostedInit,
            'checkout-preauth-init' => $hostedInit,
            'pwi-init' => $hostedInit,
            'checkout-retrieve' => $hostedRetrieve,
            'refund' => ['paymentId', 'price', 'currency', 'conversationId'],
            'refund-v2' => ['paymentId', 'price', 'currency', 'conversationId'],
            'bkm-init' => ['token', 'conversationId'],
            'bkm-retrieve' => ['paymentId', 'paymentStatus', 'basketId', 'conversationId', 'currency', 'paidPrice', 'price', 'token'],
            'apm-init' => ['paymentId', 'redirectUrl'],
            'apm-retrieve' => ['currency', 'basketId', 'conversationId', 'paidPrice', 'price', 'paymentId', 'redirectUrl'],
            // Signature POSTed to the merchant callbackUrl after the 3DS challenge.
            'callback-redirect' => ['conversationData', 'conversationId', 'mdStatus', 'paymentId', 'status'],
        ];

        return $map[$endpoint] ?? null;
    }

    /**
     * Convenience method: look up the field order for the given endpoint,
     * verify the signature, and store the result.
     */
    public function applySignature(string $secretKey, string $endpoint): void
    {
        $fieldOrder = self::getSignatureFieldOrder($endpoint);

        if ($fieldOrder === null) {
            $this->setSignatureValid(null);
            return;
        }

        $isValid = $this->verifySignature($secretKey, $fieldOrder);
        $this->setSignatureValid($isValid);
    }
}
