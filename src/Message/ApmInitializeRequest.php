<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Apm;
use Omnipay\Common\Exception\InvalidRequestException;

class ApmInitializeRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('card', 'amount');
        $this->getCard()->validate();

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'price' => $this->getAmount(),
            'paidPrice' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'installment' => $this->getInstallment(),
            'paymentChannel' => $this->getPaymentChannel(),
            'paymentGroup' => $this->getPaymentGroup(),
            'basketId' => $this->getParameter('basketId') ?: uniqid('basket_', true),
            'card' => $this->getCard(),
            'apmType' => $this->getApmType(),
            'merchantOrderId' => $this->getMerchantOrderId(),
            'countryCode' => $this->getCountryCode(),
            'merchantCallbackUrl' => $this->getReturnUrl(),
        ];
    }

    public function sendData($data): Response|RedirectResponse
    {
        $options = $this->createIyzicoOptions();
        $card = $data['card'];

        $request = new \Iyzipay\Request\CreateApmInitializeRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPrice($data['price']);
        $request->setPaidPrice($data['paidPrice']);
        $request->setCurrency($this->mapCurrency($data['currency']));
        $request->setInstallment($data['installment']);
        $request->setPaymentChannel($this->mapPaymentChannel($data['paymentChannel']));
        $request->setPaymentGroup($this->mapPaymentGroup($data['paymentGroup']));
        $request->setBasketId($data['basketId']);
        $request->setApmType($data['apmType']);
        $request->setMerchantOrderId($data['merchantOrderId']);
        $request->setCountryCode($data['countryCode']);
        $request->setMerchantCallbackUrl($data['merchantCallbackUrl']);

        $request->setPaymentCard($this->buildPaymentCard($card));
        $request->setBuyer($this->buildBuyer($card));
        $request->setShippingAddress($this->buildShippingAddress($card));
        $request->setBillingAddress($this->buildBillingAddress($card));
        $request->setBasketItems($this->buildBasketItems());

        $result = Apm::create($request, $options);

        $response = new RedirectResponse($this, $result);
        $response->setRedirectUrl($result->getRedirectUrl() ?? '');
        $response->applySignature($this->getSecretKey(), 'apm-init');

        return $response;
    }

    public function getBasketId(): ?string
    {
        return $this->getParameter('basketId');
    }

    public function setBasketId(string $value): static
    {
        return $this->setParameter('basketId', $value);
    }

    public function getApmType(): string
    {
        return $this->getParameter('apmType');
    }

    public function setApmType(string $value): static
    {
        return $this->setParameter('apmType', $value);
    }

    public function getMerchantOrderId(): string
    {
        return $this->getParameter('merchantOrderId');
    }

    public function setMerchantOrderId(string $value): static
    {
        return $this->setParameter('merchantOrderId', $value);
    }

    public function getCountryCode(): string
    {
        return $this->getParameter('countryCode');
    }

    public function setCountryCode(string $value): static
    {
        return $this->setParameter('countryCode', $value);
    }
}