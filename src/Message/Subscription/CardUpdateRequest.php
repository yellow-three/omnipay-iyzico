<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class CardUpdateRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('customerReferenceCode', 'callbackUrl');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'customerReferenceCode' => $this->getCustomerReferenceCode(),
            'callbackUrl' => $this->getCallbackUrl(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionCardUpdateRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setCustomerReferenceCode($data['customerReferenceCode']);
        $request->setCallbackUrl($data['callbackUrl']);

        $result = \Iyzipay\Model\Subscription\SubscriptionCardUpdate::update($request, $options);

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

    public function getCallbackUrl(): string
    {
        return $this->getParameter('callbackUrl');
    }

    public function setCallbackUrl(string $value): static
    {
        return $this->setParameter('callbackUrl', $value);
    }
}
