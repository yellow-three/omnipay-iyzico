<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class RetryRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('referenceCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'referenceCode' => $this->getReferenceCode(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionRetryRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setReferenceCode($data['referenceCode']);

        $result = \Iyzipay\Model\Subscription\SubscriptionRetry::update($request, $options);

        return new Response($this, $result);
    }

    public function getReferenceCode(): string
    {
        return $this->getParameter('referenceCode');
    }

    public function setReferenceCode(string $value): static
    {
        return $this->setParameter('referenceCode', $value);
    }
}
