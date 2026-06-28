<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Card;

class DeleteCardRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('cardToken', 'cardUserKey');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'cardToken' => $this->getCardToken(),
            'cardUserKey' => $this->getCardUserKey(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\DeleteCardRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setCardToken($data['cardToken']);
        $request->setCardUserKey($data['cardUserKey']);

        $result = Card::delete($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'delete-card');

        return $response;
    }
}
