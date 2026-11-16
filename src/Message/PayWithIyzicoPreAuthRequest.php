<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Exception\RuntimeException;

class PayWithIyzicoPreAuthRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('amount');
        $this->validate('returnUrl');

        $card = $this->getCard();

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'price' => $this->getAmount(),
            'paidPrice' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'basketId' => $this->getParameter('basketId') ?: uniqid('basket_', true),
            'paymentGroup' => $this->getPaymentGroup(),
            'paymentSource' => $this->getParameter('paymentSource'),
            'callbackUrl' => $this->getReturnUrl(),
            'card' => $card,
            'enabledInstallments' => $this->getParameter('enabledInstallments'),
        ];
    }

    /**
     * iyzico exposes no pre-authorization endpoint for Pay with iyzico (the SDK only has
     * PayWithIyzicoInitialize, which starts a full sale). This request used to call that
     * endpoint, silently charging the card when the caller asked for a pre-auth, so it
     * now refuses to send instead.
     *
     * @throws RuntimeException always
     */
    public function sendData($data): never
    {
        throw new RuntimeException(
            'Pay with iyzico does not support pre-authorization. Use payWithIyzico() for a sale, '
            . 'or checkoutFormPreAuth() / authorize() for a pre-authorization.'
        );
    }

    public function getBasketId(): string
    {
        return $this->getParameter('basketId');
    }

    public function setBasketId(string $value): static
    {
        return $this->setParameter('basketId', $value);
    }

    public function getEnabledInstallments(): array
    {
        return $this->getParameter('enabledInstallments') ?? [];
    }

    public function setEnabledInstallments(array $value): static
    {
        return $this->setParameter('enabledInstallments', $value);
    }

    public function getPaymentSource(): string
    {
        return $this->getParameter('paymentSource');
    }

    public function setPaymentSource(string $value): static
    {
        return $this->setParameter('paymentSource', $value);
    }
}
