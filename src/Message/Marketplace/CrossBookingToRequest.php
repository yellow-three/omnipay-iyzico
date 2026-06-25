<?php

namespace Omnipay\Iyzico\Message\Marketplace;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class CrossBookingToRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('subMerchantKey', 'price');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'subMerchantKey' => $this->getSubMerchantKey(),
            'price' => $this->getPrice(),
            'reason' => $this->getReason(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\CreateCrossBookingRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setSubMerchantKey($data['subMerchantKey']);
        $request->setPrice($data['price']);
        $request->setReason($data['reason']);

        $result = \Iyzipay\Model\CrossBookingToSubMerchant::create($request, $options);

        return new Response($this, $result);
    }

    public function getSubMerchantKey(): string
    {
        return $this->getParameter('subMerchantKey');
    }

    public function setSubMerchantKey(string $value): static
    {
        return $this->setParameter('subMerchantKey', $value);
    }

    public function getPrice(): string
    {
        return $this->getParameter('price');
    }

    public function setPrice(string $value): static
    {
        return $this->setParameter('price', $value);
    }

    public function getReason(): string
    {
        return $this->getParameter('reason');
    }

    public function setReason(string $value): static
    {
        return $this->setParameter('reason', $value);
    }
}
