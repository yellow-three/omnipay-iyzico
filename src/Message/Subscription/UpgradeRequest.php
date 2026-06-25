<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class UpgradeRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('subscriptionReferenceCode', 'newPricingPlanReferenceCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'subscriptionReferenceCode' => $this->getSubscriptionReferenceCode(),
            'newPricingPlanReferenceCode' => $this->getNewPricingPlanReferenceCode(),
            'upgradePeriod' => $this->getUpgradePeriod(),
            'useTrial' => $this->getUseTrial(),
            'resetRecurrenceCount' => $this->getResetRecurrenceCount(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionUpgradeRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setSubscriptionReferenceCode($data['subscriptionReferenceCode']);
        $request->setNewPricingPlanReferenceCode($data['newPricingPlanReferenceCode']);
        $request->setUpgradePeriod($data['upgradePeriod']);
        $request->setUseTrial($data['useTrial']);
        $request->setResetRecurrenceCount($data['resetRecurrenceCount']);

        $result = \Iyzipay\Model\Subscription\SubscriptionUpgrade::update($request, $options);

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

    public function getNewPricingPlanReferenceCode(): string
    {
        return $this->getParameter('newPricingPlanReferenceCode');
    }

    public function setNewPricingPlanReferenceCode(string $value): static
    {
        return $this->setParameter('newPricingPlanReferenceCode', $value);
    }

    public function getUpgradePeriod(): ?string
    {
        return $this->getParameter('upgradePeriod');
    }

    public function setUpgradePeriod(string $value): static
    {
        return $this->setParameter('upgradePeriod', $value);
    }

    public function getUseTrial(): ?bool
    {
        return $this->getParameter('useTrial');
    }

    public function setUseTrial(bool $value): static
    {
        return $this->setParameter('useTrial', $value);
    }

    public function getResetRecurrenceCount(): ?bool
    {
        return $this->getParameter('resetRecurrenceCount');
    }

    public function setResetRecurrenceCount(bool $value): static
    {
        return $this->setParameter('resetRecurrenceCount', $value);
    }
}
