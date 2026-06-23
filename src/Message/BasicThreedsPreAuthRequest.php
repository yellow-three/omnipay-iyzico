<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\BasicThreedsInitializePreAuth;

class BasicThreedsPreAuthRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('card');
        $this->validate('amount');
        $this->getCard()->validate();

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'price' => $this->getAmount(),
            'paidPrice' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'installment' => $this->getInstallment(),
            'card' => $this->getCard(),
            'clientIp' => $this->getClientIp(),
            'callbackUrl' => $this->getReturnUrl(),
        ];
    }

    public function sendData($data): RedirectResponse
    {
        $options = $this->createIyzicoOptions();
        $card = $data['card'];

        $request = new \Iyzipay\Request\CreateBasicPaymentRequest();
        $request->setPrice($data['price']);
        $request->setPaidPrice($data['paidPrice']);
        $request->setCurrency($this->mapCurrency($data['currency']));
        $request->setInstallment($data['installment'] ?? 1);
        $request->setBuyerEmail($card->getEmail());
        $request->setBuyerId($this->getConversationId());
        $request->setBuyerIp($this->getClientIp());
        $request->setPaymentCard($this->buildPaymentCard($card));
        $request->setCallbackUrl($data['callbackUrl']);

        $result = BasicThreedsInitializePreAuth::create($request, $options);

        $response = new RedirectResponse($this, $result);
        $response->applySignature($this->getSecretKey(), '3ds-preauth-init');

        return $response;
    }
}
