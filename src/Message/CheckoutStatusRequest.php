<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\CheckoutFormAuth;

class CheckoutStatusRequest extends AbstractRequest
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

        $request = new \Iyzipay\Request\CreateCheckoutFormAuthRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setToken($data['token']);

        $result = CheckoutFormAuth::create($request, $options);

        return new Response($this, $result);
    }

    public function getToken(): string
    {
        return $this->getParameter('token');
    }

    public function setToken(string $value): static
    {
        return $this->setParameter('token', $value);
    }
}
