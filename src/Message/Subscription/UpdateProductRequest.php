<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class UpdateProductRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('productReferenceCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'productReferenceCode' => $this->getProductReferenceCode(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionUpdateProductRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setProductReferenceCode($data['productReferenceCode']);
        $request->setName($data['name']);
        $request->setDescription($data['description']);

        $result = \Iyzipay\Model\Subscription\SubscriptionProduct::update($request, $options);

        return new Response($this, $result);
    }

    public function getProductReferenceCode(): string
    {
        return $this->getParameter('productReferenceCode');
    }

    public function setProductReferenceCode(string $value): static
    {
        return $this->setParameter('productReferenceCode', $value);
    }

    public function getName(): ?string
    {
        return $this->getParameter('name');
    }

    public function setName(string $value): static
    {
        return $this->setParameter('name', $value);
    }

}
