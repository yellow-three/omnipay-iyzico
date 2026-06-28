<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Loyalty;
use Omnipay\Common\Exception\InvalidRequestException;

class LoyaltyRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('card');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'currency' => $this->getCurrency(),
            'card' => $this->getCard(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();
        $card = $data['card'];

        $request = new \Iyzipay\Request\RetrieveLoyaltyRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setCurrency($this->mapCurrency($data['currency']));
        $request->setPaymentCard($this->buildPaymentCard($card));

        $result = Loyalty::retrieve($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'loyalty');

        return $response;
    }
}
