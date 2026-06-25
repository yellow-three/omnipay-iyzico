<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class ActivateRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('subscriptionReferenceCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'subscriptionReferenceCode' => $this->getSubscriptionReferenceCode(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionActivateRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setSubscriptionReferenceCode($data['subscriptionReferenceCode']);

        $result = \Iyzipay\Model\Subscription\SubscriptionActivate::update($request, $options);

        return new Response($this, $result);
    }

    public function getSubscriptionReferenceCode(): string
    {
        return $this->getParameter('subscriptionReferenceCode');
    }

    public function setSubscriptionReferenceCode(string $value): static
    {
        return $this->setParameter('subscriptionReferenceCode', $value);
    }
}
