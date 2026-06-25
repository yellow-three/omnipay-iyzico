<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class CreateCheckoutFormRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('pricingPlanReferenceCode', 'callbackUrl');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'pricingPlanReferenceCode' => $this->getPricingPlanReferenceCode(),
            'callbackUrl' => $this->getCallbackUrl(),
            'subscriptionInitialStatus' => $this->getSubscriptionInitialStatus(),
            'customerEmail' => $this->getCustomerEmail(),
            'customerGsmNumber' => $this->getCustomerGsmNumber(),
            'customerName' => $this->getCustomerName(),
            'customerSurname' => $this->getCustomerSurname(),
            'customerIdentityNumber' => $this->getCustomerIdentityNumber(),
            'customerBillingAddress' => $this->getCustomerBillingAddress(),
            'customerBillingCity' => $this->getCustomerBillingCity(),
            'customerBillingCountry' => $this->getCustomerBillingCountry(),
            'customerBillingZipCode' => $this->getCustomerBillingZipCode(),
            'customerBillingContactName' => $this->getCustomerBillingContactName(),
            'customerShippingAddress' => $this->getCustomerShippingAddress(),
            'customerShippingCity' => $this->getCustomerShippingCity(),
            'customerShippingCountry' => $this->getCustomerShippingCountry(),
            'customerShippingZipCode' => $this->getCustomerShippingZipCode(),
            'customerShippingContactName' => $this->getCustomerShippingContactName(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $customer = new \Iyzipay\Model\Customer();
        $customer->setName($data['customerName']);
        $customer->setSurname($data['customerSurname']);
        $customer->setEmail($data['customerEmail']);
        $customer->setGsmNumber($data['customerGsmNumber']);
        $customer->setIdentityNumber($data['customerIdentityNumber']);
        $customer->setBillingContactName($data['customerBillingContactName']);
        $customer->setBillingAddress($data['customerBillingAddress']);
        $customer->setBillingCity($data['customerBillingCity']);
        $customer->setBillingCountry($data['customerBillingCountry']);
        $customer->setBillingZipCode($data['customerBillingZipCode']);
        $customer->setShippingContactName($data['customerShippingContactName']);
        $customer->setShippingAddress($data['customerShippingAddress']);
        $customer->setShippingCity($data['customerShippingCity']);
        $customer->setShippingCountry($data['customerShippingCountry']);
        $customer->setShippingZipCode($data['customerShippingZipCode']);

        $request = new \Iyzipay\Request\Subscription\SubscriptionCreateCheckoutFormRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPricingPlanReferenceCode($data['pricingPlanReferenceCode']);
        $request->setCallbackUrl($data['callbackUrl']);
        $request->setSubscriptionInitialStatus($data['subscriptionInitialStatus']);
        $request->setCustomer($customer);

        $result = \Iyzipay\Model\Subscription\SubscriptionCreateCheckoutForm::create($request, $options);

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

    public function getCallbackUrl(): string
    {
        return $this->getParameter('callbackUrl');
    }

    public function setCallbackUrl(string $value): static
    {
        return $this->setParameter('callbackUrl', $value);
    }

    public function getSubscriptionInitialStatus(): ?string
    {
        return $this->getParameter('subscriptionInitialStatus');
    }

    public function setSubscriptionInitialStatus(string $value): static
    {
        return $this->setParameter('subscriptionInitialStatus', $value);
    }

    public function getCustomerEmail(): ?string
    {
        return $this->getParameter('customerEmail');
    }

    public function setCustomerEmail(string $value): static
    {
        return $this->setParameter('customerEmail', $value);
    }

    public function getCustomerGsmNumber(): ?string
    {
        return $this->getParameter('customerGsmNumber');
    }

    public function setCustomerGsmNumber(string $value): static
    {
        return $this->setParameter('customerGsmNumber', $value);
    }

    public function getCustomerName(): ?string
    {
        return $this->getParameter('customerName');
    }

    public function setCustomerName(string $value): static
    {
        return $this->setParameter('customerName', $value);
    }

    public function getCustomerSurname(): ?string
    {
        return $this->getParameter('customerSurname');
    }

    public function setCustomerSurname(string $value): static
    {
        return $this->setParameter('customerSurname', $value);
    }

    public function getCustomerIdentityNumber(): ?string
    {
        return $this->getParameter('customerIdentityNumber');
    }

    public function setCustomerIdentityNumber(string $value): static
    {
        return $this->setParameter('customerIdentityNumber', $value);
    }

    public function getCustomerBillingAddress(): ?string
    {
        return $this->getParameter('customerBillingAddress');
    }

    public function setCustomerBillingAddress(string $value): static
    {
        return $this->setParameter('customerBillingAddress', $value);
    }

    public function getCustomerBillingCity(): ?string
    {
        return $this->getParameter('customerBillingCity');
    }

    public function setCustomerBillingCity(string $value): static
    {
        return $this->setParameter('customerBillingCity', $value);
    }

    public function getCustomerBillingCountry(): ?string
    {
        return $this->getParameter('customerBillingCountry');
    }

    public function setCustomerBillingCountry(string $value): static
    {
        return $this->setParameter('customerBillingCountry', $value);
    }

    public function getCustomerBillingZipCode(): ?string
    {
        return $this->getParameter('customerBillingZipCode');
    }

    public function setCustomerBillingZipCode(string $value): static
    {
        return $this->setParameter('customerBillingZipCode', $value);
    }

    public function getCustomerBillingContactName(): ?string
    {
        return $this->getParameter('customerBillingContactName');
    }

    public function setCustomerBillingContactName(string $value): static
    {
        return $this->setParameter('customerBillingContactName', $value);
    }

    public function getCustomerShippingAddress(): ?string
    {
        return $this->getParameter('customerShippingAddress');
    }

    public function setCustomerShippingAddress(string $value): static
    {
        return $this->setParameter('customerShippingAddress', $value);
    }

    public function getCustomerShippingCity(): ?string
    {
        return $this->getParameter('customerShippingCity');
    }

    public function setCustomerShippingCity(string $value): static
    {
        return $this->setParameter('customerShippingCity', $value);
    }

    public function getCustomerShippingCountry(): ?string
    {
        return $this->getParameter('customerShippingCountry');
    }

    public function setCustomerShippingCountry(string $value): static
    {
        return $this->setParameter('customerShippingCountry', $value);
    }

    public function getCustomerShippingZipCode(): ?string
    {
        return $this->getParameter('customerShippingZipCode');
    }

    public function setCustomerShippingZipCode(string $value): static
    {
        return $this->setParameter('customerShippingZipCode', $value);
    }

    public function getCustomerShippingContactName(): ?string
    {
        return $this->getParameter('customerShippingContactName');
    }

    public function setCustomerShippingContactName(string $value): static
    {
        return $this->setParameter('customerShippingContactName', $value);
    }
}
