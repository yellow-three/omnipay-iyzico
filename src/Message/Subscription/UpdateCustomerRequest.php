<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class UpdateCustomerRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('customerReferenceCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'customerReferenceCode' => $this->getCustomerReferenceCode(),
            'email' => $this->getEmail(),
            'gsmNumber' => $this->getGsmNumber(),
            'name' => $this->getName(),
            'surname' => $this->getSurname(),
            'identityNumber' => $this->getIdentityNumber(),
            'contactEmail' => $this->getContactEmail(),
            'contactGsmNumber' => $this->getContactGsmNumber(),
            'billingAddress' => $this->getBillingAddress(),
            'billingCity' => $this->getBillingCity(),
            'billingDistrict' => $this->getBillingDistrict(),
            'billingCountry' => $this->getBillingCountry(),
            'billingZipCode' => $this->getBillingZipCode(),
            'billingContactName' => $this->getBillingContactName(),
            'shippingAddress' => $this->getShippingAddress(),
            'shippingCity' => $this->getShippingCity(),
            'shippingDistrict' => $this->getShippingDistrict(),
            'shippingCountry' => $this->getShippingCountry(),
            'shippingZipCode' => $this->getShippingZipCode(),
            'shippingContactName' => $this->getShippingContactName(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $customer = new \Iyzipay\Model\Customer();
        $customer->setEmail($data['email'] ?? null);
        $customer->setGsmNumber($data['gsmNumber'] ?? null);
        $customer->setName($data['name'] ?? null);
        $customer->setSurname($data['surname'] ?? null);
        $customer->setIdentityNumber($data['identityNumber'] ?? null);
        $customer->setBillingAddress($data['billingAddress'] ?? null);
        $customer->setBillingCity($data['billingCity'] ?? null);
        $customer->setBillingDistrict($data['billingDistrict'] ?? null);
        $customer->setBillingCountry($data['billingCountry'] ?? null);
        $customer->setBillingZipCode($data['billingZipCode'] ?? null);
        $customer->setBillingContactName($data['billingContactName'] ?? null);
        $customer->setShippingAddress($data['shippingAddress'] ?? null);
        $customer->setShippingCity($data['shippingCity'] ?? null);
        $customer->setShippingDistrict($data['shippingDistrict'] ?? null);
        $customer->setShippingCountry($data['shippingCountry'] ?? null);
        $customer->setShippingZipCode($data['shippingZipCode'] ?? null);
        $customer->setShippingContactName($data['shippingContactName'] ?? null);

        $request = new \Iyzipay\Request\Subscription\SubscriptionUpdateCustomerRequest();
        $request->setCustomer($customer);
        $request->setCustomerReferenceCode($data['customerReferenceCode']);

        $result = \Iyzipay\Model\Subscription\SubscriptionCustomer::update($request, $options);

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

    public function getGsmNumber(): ?string
    {
        return $this->getParameter('gsmNumber');
    }

    public function setGsmNumber(string $value): static
    {
        return $this->setParameter('gsmNumber', $value);
    }

    public function getName(): ?string
    {
        return $this->getParameter('name');
    }

    public function setName(string $value): static
    {
        return $this->setParameter('name', $value);
    }

    public function getSurname(): ?string
    {
        return $this->getParameter('surname');
    }

    public function setSurname(string $value): static
    {
        return $this->setParameter('surname', $value);
    }

    public function getContactEmail(): ?string
    {
        return $this->getParameter('contactEmail');
    }

    public function setContactEmail(string $value): static
    {
        return $this->setParameter('contactEmail', $value);
    }

    public function getContactGsmNumber(): ?string
    {
        return $this->getParameter('contactGsmNumber');
    }

    public function setContactGsmNumber(string $value): static
    {
        return $this->setParameter('contactGsmNumber', $value);
    }

    public function getBillingAddress(): ?string
    {
        return $this->getParameter('billingAddress');
    }

    public function setBillingAddress(string $value): static
    {
        return $this->setParameter('billingAddress', $value);
    }

    public function getBillingCity(): ?string
    {
        return $this->getParameter('billingCity');
    }

    public function setBillingCity(string $value): static
    {
        return $this->setParameter('billingCity', $value);
    }

    public function getBillingDistrict(): ?string
    {
        return $this->getParameter('billingDistrict');
    }

    public function setBillingDistrict(string $value): static
    {
        return $this->setParameter('billingDistrict', $value);
    }

    public function getBillingCountry(): ?string
    {
        return $this->getParameter('billingCountry');
    }

    public function setBillingCountry(string $value): static
    {
        return $this->setParameter('billingCountry', $value);
    }

    public function getBillingZipCode(): ?string
    {
        return $this->getParameter('billingZipCode');
    }

    public function setBillingZipCode(string $value): static
    {
        return $this->setParameter('billingZipCode', $value);
    }

    public function getBillingContactName(): ?string
    {
        return $this->getParameter('billingContactName');
    }

    public function setBillingContactName(string $value): static
    {
        return $this->setParameter('billingContactName', $value);
    }

    public function getShippingAddress(): ?string
    {
        return $this->getParameter('shippingAddress');
    }

    public function setShippingAddress(string $value): static
    {
        return $this->setParameter('shippingAddress', $value);
    }

    public function getShippingCity(): ?string
    {
        return $this->getParameter('shippingCity');
    }

    public function setShippingCity(string $value): static
    {
        return $this->setParameter('shippingCity', $value);
    }

    public function getShippingDistrict(): ?string
    {
        return $this->getParameter('shippingDistrict');
    }

    public function setShippingDistrict(string $value): static
    {
        return $this->setParameter('shippingDistrict', $value);
    }

    public function getShippingCountry(): ?string
    {
        return $this->getParameter('shippingCountry');
    }

    public function setShippingCountry(string $value): static
    {
        return $this->setParameter('shippingCountry', $value);
    }

    public function getShippingZipCode(): ?string
    {
        return $this->getParameter('shippingZipCode');
    }

    public function setShippingZipCode(string $value): static
    {
        return $this->setParameter('shippingZipCode', $value);
    }

    public function getShippingContactName(): ?string
    {
        return $this->getParameter('shippingContactName');
    }

    public function setShippingContactName(string $value): static
    {
        return $this->setParameter('shippingContactName', $value);
    }
}
