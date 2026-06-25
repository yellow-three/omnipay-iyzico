<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class CreateSubscriptionWithCustomerRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('pricingPlanReferenceCode', 'customerReferenceCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'pricingPlanReferenceCode' => $this->getPricingPlanReferenceCode(),
            'subscriptionInitialStatus' => $this->getSubscriptionInitialStatus(),
            'customerReferenceCode' => $this->getCustomerReferenceCode(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionCreateWithCustomerRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPricingPlanReferenceCode($data['pricingPlanReferenceCode']);
        $request->setSubscriptionInitialStatus($data['subscriptionInitialStatus']);
        $request->setCustomerReferenceCode($data['customerReferenceCode']);

        $result = \Iyzipay\Model\Subscription\SubscriptionCreateWithCustomer::create($request, $options);

        return new Response($this, $result);
    }

    public function getPricingPlanReferenceCode(): string
    {
        return $this->getParameter('pricingPlanReferenceCode');
    }

    public function setPricingPlanReferenceCode(string $value): static
    {
        return $this->setParameter('pricingPlanReferenceCode', $value);
    }

    public function getCustomerReferenceCode(): string
    {
        return $this->getParameter('customerReferenceCode');
    }

    public function setCustomerReferenceCode(string $value): static
    {
        return $this->setParameter('customerReferenceCode', $value);
    }

    public function getSubscriptionInitialStatus(): ?string
    {
        return $this->getParameter('subscriptionInitialStatus');
    }

    public function setSubscriptionInitialStatus(string $value): static
    {
        return $this->setParameter('subscriptionInitialStatus', $value);
    }
}
