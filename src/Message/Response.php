<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;

class Response extends AbstractResponse implements RedirectResponseInterface
{
    public function __construct(RequestInterface $request, mixed $data)
    {
        $this->request = $request;

        if (is_object($data) && method_exists($data, 'getRawResult')) {
            $this->data = json_decode($data->getRawResult(), true);
        } elseif (is_string($data)) {
            $this->data = json_decode($data, true);
        } else {
            $this->data = $data;
        }
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
