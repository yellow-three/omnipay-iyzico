<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Iyzilink\IyziLinkDeleteProduct;

class IyziLinkDeleteProductRequest extends AbstractRequest
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

        $result = IyziLinkDeleteProduct::create(new \Iyzipay\Request(), $options, $data['token']);

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