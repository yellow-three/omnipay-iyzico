<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\CheckoutFormInitialize;

class CheckoutRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('amount', 'basketId');

        $card = $this->getCard();

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'price' => $this->getAmount(),
            'paidPrice' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'basketId' => $this->getParameter('basketId'),
            'paymentGroup' => $this->getPaymentGroup(),
            'callbackUrl' => $this->getReturnUrl(),
            'card' => $card,
            'enabledInstallments' => $this->getParameter('enabledInstallments') ?? [2, 3, 6, 9],
        ];
    }

    public function sendData($data): Response|RedirectResponse
    {
        $options = $this->createIyzicoOptions();
        $card = $data['card'];

        $request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
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
        $request->setEnabledInstallments($data['enabledInstallments']);

        $result = CheckoutFormInitialize::create($request, $options);

        $response = new RedirectResponse($this, $result);
        $response->setRedirectUrl($result->getPaymentPageUrl() ?? '');
        $response->setRedirectMethod('GET');
        $response->applySignature($this->getSecretKey(), 'checkout-init');

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
        return $this->getParameter('enabledInstallments') ?? [2, 3, 6, 9];
    }

    public function setEnabledInstallments(array $value): static
    {
        return $this->setParameter('enabledInstallments', $value);
    }
}
