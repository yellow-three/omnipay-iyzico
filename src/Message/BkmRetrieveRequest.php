<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Bkm;

class BkmRetrieveRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('token');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'token' => $this->getToken(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\RetrieveBkmRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setToken($data['token']);

        $result = Bkm::retrieve($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'bkm-retrieve');

        return $response;
    }

    public function getToken(): ?string
    {
        return $this->getParameter('token');
    }

    public function setToken($value)
    {
        return $this->setParameter('token', $value);
    }
}