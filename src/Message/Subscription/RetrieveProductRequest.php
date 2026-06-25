<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class RetrieveProductRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('productReferenceCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'productReferenceCode' => $this->getProductReferenceCode(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionRetrieveProductRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setProductReferenceCode($data['productReferenceCode']);

        $result = \Iyzipay\Model\Subscription\SubscriptionProduct::retrieve($request, $options);

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
}
