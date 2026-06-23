<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\BkmInitialize;

class BkmInitializeRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('amount');

        $card = $this->getCard();

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'price' => $this->getAmount(),
            'paidPrice' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'basketId' => $this->getParameter('basketId') ?: uniqid('basket_', true),
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

        $request = new \Iyzipay\Request\CreateBkmInitializeRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPrice($data['price']);
        $request->setCurrency($this->mapCurrency($data['currency']));
        $request->setBasketId($data['basketId']);
        $request->setPaymentGroup($this->mapPaymentGroup($data['paymentGroup']));
        $request->setCallbackUrl($data['callbackUrl']);
        $request->setEnabledInstallments($data['enabledInstallments']);

        if ($card) {
            $request->setBuyer($this->buildBuyer($card));
            $request->setShippingAddress($this->buildShippingAddress($card));
            $request->setBillingAddress($this->buildBillingAddress($card));
        }

        $request->setBasketItems($this->buildBasketItems());

        $result = BkmInitialize::create($request, $options);

        $response = new RedirectResponse($this, $result);
        $response->setRedirectUrl($result->getHtmlContent() ?? '');
        $response->setRedirectMethod('POST');
        $response->applySignature($this->getSecretKey(), 'bkm-init');

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