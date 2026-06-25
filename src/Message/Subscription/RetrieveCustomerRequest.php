<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class RetrieveCustomerRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('customerReferenceCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'customerReferenceCode' => $this->getCustomerReferenceCode(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionRetrieveCustomerRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setCustomerReferenceCode($data['customerReferenceCode']);

        $result = \Iyzipay\Model\Subscription\SubscriptionCustomer::retrieve($request, $options);

        return new Response($this, $result);
    }

    public function getCustomerReferenceCode(): string
    {
        return $this->getParameter('customerReferenceCode');
    }

    public function setCustomerReferenceCode(string $value): static
    {
        return $this->setParameter('customerReferenceCode', $value);
    }
}
