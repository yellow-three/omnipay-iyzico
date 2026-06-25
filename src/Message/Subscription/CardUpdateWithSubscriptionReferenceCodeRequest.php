<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class CardUpdateWithSubscriptionReferenceCodeRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('subscriptionReferenceCode', 'callbackUrl');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'subscriptionReferenceCode' => $this->getSubscriptionReferenceCode(),
            'callbackUrl' => $this->getCallbackUrl(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionCardUpdateWithSubscriptionReferenceCodeRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setSubscriptionReferenceCode($data['subscriptionReferenceCode']);
        $request->setCallbackUrl($data['callbackUrl']);

        $result = \Iyzipay\Model\Subscription\SubscriptionCardUpdate::updateWithSubscriptionReferenceCode($request, $options);

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

    public function getCallbackUrl(): string
    {
        return $this->getParameter('callbackUrl');
    }

    public function setCallbackUrl(string $value): static
    {
        return $this->setParameter('callbackUrl', $value);
    }
}
