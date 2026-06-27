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
        return $this->data['paymentTransactionId'] ?? null;
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
        $parts = [];
        $parts[] = $secretKey;

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
     * @return array|null Ordered field names, or null if the endpoint is unknown
     */
    public static function getSignatureFieldOrder(string $endpoint): ?array
    {
        $map = [
            'non-3ds' => ['paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price'],
            'preauth' => ['paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price'],
            'postauth' => ['paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price'],
            'payment-detail' => ['paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price'],
            '3ds-init' => ['paymentId', 'conversationId'],
            '3ds-preauth-init' => ['paymentId', 'conversationId'],
            '3ds-auth' => ['paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price'],
            '3ds-v2-auth' => ['paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price'],
            'checkout-init' => ['conversationId', 'token'],
            'pwi-init' => ['conversationId', 'token'],
            'checkout-preauth-init' => ['conversationId', 'token'],
            'checkout-retrieve' => ['paymentStatus', 'paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price', 'token'],
            'refund' => ['paymentId', 'price', 'currency', 'conversationId'],
            'refund-v2' => ['paymentId', 'price', 'currency', 'conversationId'],
            // BKM endpoints
            'bkm-init' => ['paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price'],
            'basic-bkm-init' => ['paymentId', 'currency', 'conversationId', 'paidPrice', 'price'],
            'bkm-retrieve' => ['paymentId', 'conversationId', 'paymentStatus'],
            // APM endpoints
            'apm-init' => ['paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price'],
            'apm' => ['paymentId', 'currency', 'conversationId', 'price'],
            // Marketplace endpoints (no signature in response)
            'marketplace-create-sub-merchant' => [],
            'marketplace-update-sub-merchant' => [],
            'marketplace-retrieve-sub-merchant' => [],
            'marketplace-approve-payment' => [],
            'marketplace-disapprove-payment' => [],
            'marketplace-cross-booking-from' => [],
            'marketplace-cross-booking-to' => [],
            'marketplace-update-payment-item' => [],
            // iyzico Link endpoints
            'iyzilink-create' => ['paymentId', 'conversationId'],
            // Reporting endpoints (no signature in response)
            'reporting-payment-detail' => [],
            'reporting-payment-transaction' => [],
            'reporting-scroll-transaction' => [],
            // Callback redirect signature verification
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
