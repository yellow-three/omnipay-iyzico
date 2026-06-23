<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\BasicBkmInitialize;

class BasicBkmInitializeRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('amount');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'price' => $this->getAmount(),
            'paidPrice' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'basketId' => $this->getParameter('basketId') ?: uniqid('basket_', true),
            'paymentGroup' => $this->getPaymentGroup(),
            'callbackUrl' => $this->getReturnUrl(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();
        $card = $this->getCard();

        $request = new \Iyzipay\Request\CreateBasicBkmInitializeRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPrice($data['price']);
        $request->setCallbackUrl($data['callbackUrl']);
        $request->setBuyerId($data['conversationId']);
        $request->setBuyerIp($this->getClientIp() ?? '127.0.0.1');
        
        if ($card) {
            $request->setBuyerEmail($card->getEmail() ?? '');
        } else {
            $request->setBuyerEmail('');
        }

        $result = BasicBkmInitialize::create($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'basic-bkm-init');

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
}