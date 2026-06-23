<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Iyzilink\IyziLinkFastLink;

class IyziLinkCreateFastLinkRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('description', 'price', 'currencyCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'description' => $this->getDescription(),
            'price' => $this->getPrice(),
            'currencyCode' => $this->getCurrencyCode(),
            'sourceType' => $this->getSourceType() ?: 'WEB',
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Iyzilink\IyziLinkCreateFastLinkRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setDescription($data['description']);
        $request->setPrice($data['price']);
        $request->setCurrencyCode($data['currencyCode']);
        $request->setSourceType($data['sourceType']);

        $result = IyziLinkFastLink::create($request, $options);

        return new Response($this, $result);
    }

    public function getDescription(): string
    {
        return $this->getParameter('description');
    }

    public function setDescription(string $value): static
    {
        return $this->setParameter('description', $value);
    }

    public function getPrice(): float
    {
        return (float) $this->getParameter('price');
    }

    public function setPrice(float $value): static
    {
        return $this->setParameter('price', $value);
    }

    public function getCurrencyCode(): string
    {
        return $this->getParameter('currencyCode');
    }

    public function setCurrencyCode(string $value): static
    {
        return $this->setParameter('currencyCode', $value);
    }

    public function getSourceType(): string
    {
        return $this->getParameter('sourceType') ?: 'WEB';
    }

    public function setSourceType(string $value): static
    {
        return $this->setParameter('sourceType', $value);
    }
}