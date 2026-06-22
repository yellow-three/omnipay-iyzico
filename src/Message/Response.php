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
}
