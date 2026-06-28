<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\CardList;

class ListCardsRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('cardUserKey');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'cardUserKey' => $this->getCardUserKey(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\RetrieveCardListRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setCardUserKey($data['cardUserKey']);

        $result = CardList::retrieve($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'list-cards');

        return $response;
    }
}
