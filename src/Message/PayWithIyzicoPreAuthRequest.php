<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\PayWithIyzicoInitialize;

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

    public function sendData($data): RedirectResponse
    {
        $options = $this->createIyzicoOptions();
        $card = $data['card'];

        $request = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPrice($data['price']);
        $request->setPaidPrice($data['paidPrice']);
        $request->setCurrency($this->mapCurrency($data['currency']));
        $request->setBasketId($data['basketId']);
        $request->setPaymentGroup($this->mapPaymentGroup($data['paymentGroup']));
        $request->setCallbackUrl($data['callbackUrl']);

        if ($card) {
            $request->setBuyer($this->buildBuyer($card));
            $request->setShippingAddress($this->buildShippingAddress($card));
            $request->setBillingAddress($this->buildBillingAddress($card));
        }

        $request->setBasketItems($this->buildBasketItems());

        if (!empty($data['paymentSource'])) {
            $request->setPaymentSource($data['paymentSource']);
        }

        if (!empty($data['enabledInstallments'])) {
            $request->setEnabledInstallments($data['enabledInstallments']);
        }

        $result = PayWithIyzicoInitialize::create($request, $options);

        $response = new RedirectResponse($this, $result);
        $response->setRedirectUrl($result->getPayWithIyzicoPageUrl() ?? '');
        $response->setRedirectMethod('GET');
        $response->applySignature($this->getSecretKey(), 'pwi-init');

        return $response;
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
