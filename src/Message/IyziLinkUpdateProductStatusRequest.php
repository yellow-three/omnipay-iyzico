<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Iyzilink\IyziLinkUpdateProductStatus;

class IyziLinkUpdateProductStatusRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('token', 'productStatus');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'token' => $this->getToken(),
            'productStatus' => $this->getProductStatus(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Iyzilink\IyziLinkUpdateProductStatusRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setToken($data['token']);
        $request->setProductStatus($data['productStatus']);

        $result = IyziLinkUpdateProductStatus::create($request, $options);

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

    public function getProductStatus(): string
    {
        return $this->getParameter('productStatus');
    }

    public function setProductStatus(string $value): static
    {
        return $this->setParameter('productStatus', $value);
    }
}