<?php

namespace Omnipay\Iyzico;

use Omnipay\Common\AbstractGateway;

class Gateway extends AbstractGateway
{
    public function getName(): string
    {
        return 'Iyzico';
    }

    public function getDefaultParameters(): array
    {
        return [
            'apiKey' => '',
            'secretKey' => '',
            'baseUrl' => 'https://sandbox-api.iyzipay.com',
            'testMode' => false,
            'locale' => 'TR',
            'conversationId' => '',
            'paymentChannel' => 'WEB',
            'paymentGroup' => 'PRODUCT',
            'currency' => 'TRY',
            'installment' => 0,
            'identityNumber' => '',
            'secure3d' => true,
        ];
    }

    public function getApiKey(): string
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey(string $value): static
    {
        return $this->setParameter('apiKey', $value);
    }

    public function getSecretKey(): string
    {
        return $this->getParameter('secretKey');
    }

    public function setSecretKey(string $value): static
    {
        return $this->setParameter('secretKey', $value);
    }

    public function getBaseUrl(): string
    {
        return $this->getParameter('baseUrl');
    }

    public function setBaseUrl(string $value): static
    {
        return $this->setParameter('baseUrl', $value);
    }

    public function getLocale(): string
    {
        return $this->getParameter('locale');
    }

    public function setLocale(string $value): static
    {
        return $this->setParameter('locale', $value);
    }

    public function getConversationId(): string
    {
        return $this->getParameter('conversationId');
    }

    public function setConversationId(string $value): static
    {
        return $this->setParameter('conversationId', $value);
    }

    public function getPaymentChannel(): string
    {
        return $this->getParameter('paymentChannel');
    }

    public function setPaymentChannel(string $value): static
    {
        return $this->setParameter('paymentChannel', $value);
    }

    public function getPaymentGroup(): string
    {
        return $this->getParameter('paymentGroup');
    }

    public function setPaymentGroup(string $value): static
    {
        return $this->setParameter('paymentGroup', $value);
    }

    public function getInstallment(): int
    {
        return $this->getParameter('installment');
    }

    public function setInstallment(int $value): static
    {
        return $this->setParameter('installment', $value);
    }

    public function getIdentityNumber(): string
    {
        return $this->getParameter('identityNumber');
    }

    public function setIdentityNumber(string $value): static
    {
        return $this->setParameter('identityNumber', $value);
    }

    public function getSecure3d(): bool
    {
        return $this->getParameter('secure3d');
    }

    public function setSecure3d(bool $value): static
    {
        return $this->setParameter('secure3d', $value);
    }

    public function purchase(array $parameters = []): Message\PurchaseRequest
    {
        return $this->createRequest(Message\PurchaseRequest::class, $parameters);
    }

    public function authorize(array $parameters = []): Message\AuthorizeRequest
    {
        return $this->createRequest(Message\AuthorizeRequest::class, $parameters);
    }

    public function capture(array $parameters = []): Message\CaptureRequest
    {
        return $this->createRequest(Message\CaptureRequest::class, $parameters);
    }

    public function refund(array $parameters = []): Message\RefundRequest
    {
        return $this->createRequest(Message\RefundRequest::class, $parameters);
    }

    public function void(array $parameters = []): Message\VoidRequest
    {
        return $this->createRequest(Message\VoidRequest::class, $parameters);
    }

    public function fetchTransaction(array $parameters = []): Message\FetchTransactionRequest
    {
        return $this->createRequest(Message\FetchTransactionRequest::class, $parameters);
    }

    public function checkout(array $parameters = []): Message\CheckoutRequest
    {
        return $this->createRequest(Message\CheckoutRequest::class, $parameters);
    }

    public function checkoutStatus(array $parameters = []): Message\CheckoutStatusRequest
    {
        return $this->createRequest(Message\CheckoutStatusRequest::class, $parameters);
    }

    public function completePurchase(array $parameters = []): Message\CompletePurchaseRequest
    {
        return $this->createRequest(Message\CompletePurchaseRequest::class, $parameters);
    }
}
