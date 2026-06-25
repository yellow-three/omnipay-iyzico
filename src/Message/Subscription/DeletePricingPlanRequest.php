<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class DeletePricingPlanRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('pricingPlanReferenceCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'pricingPlanReferenceCode' => $this->getPricingPlanReferenceCode(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionDeletePricingPlanRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPricingPlanReferenceCode($data['pricingPlanReferenceCode']);

        $result = \Iyzipay\Model\Subscription\SubscriptionPricingPlan::delete($request, $options);

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
}
