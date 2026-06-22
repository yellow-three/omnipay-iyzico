<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\PayWithIyzico;

class PayWithIyzicoRetrieveRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('token');

        return [
            'token' => $this->getToken(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\RetrievePayWithIyzicoRequest();
        $request->setToken($data['token']);

        $result = PayWithIyzico::retrieve($request, $options);

        return new Response($this, $result);
    }

    public function getToken(): string
    {
        return $this->getParameter('token');
    }

    public function setToken($value): static
    {
        return $this->setParameter('token', $value);
    }
}
