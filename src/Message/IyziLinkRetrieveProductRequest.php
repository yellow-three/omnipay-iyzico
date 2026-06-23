<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Iyzilink\IyziLinkRetrieveProduct;
use Omnipay\Common\Exception\InvalidRequestException;

class IyziLinkRetrieveProductRequest extends AbstractRequest
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

        $result = IyziLinkRetrieveProduct::create(null, $options, $data['token']);

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