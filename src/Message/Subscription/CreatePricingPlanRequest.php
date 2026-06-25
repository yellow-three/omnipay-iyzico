<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class CreatePricingPlanRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('name', 'productReferenceCode', 'price', 'currencyCode', 'paymentInterval');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'name' => $this->getName(),
            'productReferenceCode' => $this->getProductReferenceCode(),
            'price' => $this->getPrice(),
            'currencyCode' => $this->getCurrencyCode(),
            'paymentInterval' => $this->getPaymentInterval(),
            'paymentIntervalCount' => $this->getPaymentIntervalCount(),
            'trialPeriodDays' => $this->getTrialPeriodDays(),
            'planPaymentType' => $this->getPlanPaymentType(),
            'recurrenceCount' => $this->getRecurrenceCount(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionCreatePricingPlanRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setName($data['name']);
        $request->setProductReferenceCode($data['productReferenceCode']);
        $request->setPrice($data['price']);
        $request->setCurrencyCode($data['currencyCode']);
        $request->setPaymentInterval($data['paymentInterval']);
        $request->setPaymentIntervalCount($data['paymentIntervalCount']);
        $request->setTrialPeriodDays($data['trialPeriodDays']);
        $request->setPlanPaymentType($data['planPaymentType']);
        $request->setRecurrenceCount($data['recurrenceCount']);

        $result = \Iyzipay\Model\Subscription\SubscriptionPricingPlan::create($request, $options);

        return new Response($this, $result);
    }

    public function getName(): string
    {
        return $this->getParameter('name');
    }

    public function setName(string $value): static
    {
        return $this->setParameter('name', $value);
    }

    public function getProductReferenceCode(): string
    {
        return $this->getParameter('productReferenceCode');
    }

    public function setProductReferenceCode(string $value): static
    {
        return $this->setParameter('productReferenceCode', $value);
    }

    public function getPrice(): string
    {
        return $this->getParameter('price');
    }

    public function setPrice(string $value): static
    {
        return $this->setParameter('price', $value);
    }

    public function getCurrencyCode(): string
    {
        return $this->getParameter('currencyCode');
    }

    public function setCurrencyCode(string $value): static
    {
        return $this->setParameter('currencyCode', $value);
    }

    public function getPaymentInterval(): string
    {
        return $this->getParameter('paymentInterval');
    }

    public function setPaymentInterval(string $value): static
    {
        return $this->setParameter('paymentInterval', $value);
    }

    public function getPaymentIntervalCount(): ?int
    {
        return $this->getParameter('paymentIntervalCount');
    }

    public function setPaymentIntervalCount(int $value): static
    {
        return $this->setParameter('paymentIntervalCount', $value);
    }

    public function getTrialPeriodDays(): ?int
    {
        return $this->getParameter('trialPeriodDays');
    }

    public function setTrialPeriodDays(int $value): static
    {
        return $this->setParameter('trialPeriodDays', $value);
    }

    public function getPlanPaymentType(): ?string
    {
        return $this->getParameter('planPaymentType');
    }

    public function setPlanPaymentType(string $value): static
    {
        return $this->setParameter('planPaymentType', $value);
    }

    public function getRecurrenceCount(): ?int
    {
        return $this->getParameter('recurrenceCount');
    }

    public function setRecurrenceCount(int $value): static
    {
        return $this->setParameter('recurrenceCount', $value);
    }
}
