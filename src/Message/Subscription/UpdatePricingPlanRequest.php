<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class UpdatePricingPlanRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('pricingPlanReferenceCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'pricingPlanReferenceCode' => $this->getPricingPlanReferenceCode(),
            'name' => $this->getName(),
            'trialPeriodDays' => $this->getTrialPeriodDays(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionUpdatePricingPlanRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPricingPlanReferenceCode($data['pricingPlanReferenceCode']);
        $request->setName($data['name']);
        $request->setTrialPeriodDays($data['trialPeriodDays']);

        $result = \Iyzipay\Model\Subscription\SubscriptionPricingPlan::update($request, $options);

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

    public function getName(): ?string
    {
        return $this->getParameter('name');
    }

    public function setName(string $value): static
    {
        return $this->setParameter('name', $value);
    }

    public function getTrialPeriodDays(): ?int
    {
        return $this->getParameter('trialPeriodDays');
    }

    public function setTrialPeriodDays(int $value): static
    {
        return $this->setParameter('trialPeriodDays', $value);
    }
}
