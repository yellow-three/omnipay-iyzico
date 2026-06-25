<?php

namespace Omnipay\Iyzico\Message\Marketplace;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class CreateSubMerchantRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('subMerchantExternalId', 'subMerchantType', 'price', 'currency');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'subMerchantExternalId' => $this->getSubMerchantExternalId(),
            'email' => $this->getEmail(),
            'gsmNumber' => $this->getGsmNumber(),
            'address' => $this->getAddress(),
            'iban' => $this->getIban(),
            'swiftCode' => $this->getSwiftCode(),
            'taxOffice' => $this->getTaxOffice(),
            'contactName' => $this->getContactName(),
            'contactSurname' => $this->getContactSurname(),
            'legalCompanyTitle' => $this->getLegalCompanyTitle(),
            'identityNumber' => $this->getIdentityNumber(),
            'taxNumber' => $this->getTaxNumber(),
            'subMerchantType' => $this->getSubMerchantType(),
            'currency' => $this->getCurrency(),
            'name' => $this->getName(),
            'price' => $this->getPrice(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\CreateSubMerchantRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setSubMerchantExternalId($data['subMerchantExternalId']);
        $request->setEmail($data['email']);
        $request->setGsmNumber($data['gsmNumber']);
        $request->setAddress($data['address']);
        $request->setIban($data['iban']);
        $request->setSwiftCode($data['swiftCode']);
        $request->setTaxOffice($data['taxOffice']);
        $request->setContactName($data['contactName']);
        $request->setContactSurname($data['contactSurname']);
        $request->setLegalCompanyTitle($data['legalCompanyTitle']);
        $request->setIdentityNumber($data['identityNumber']);
        $request->setTaxNumber($data['taxNumber']);
        $request->setSubMerchantType($data['subMerchantType']);
        $request->setCurrency($this->mapCurrency($data['currency']));
        $request->setName($data['name']);

        $result = \Iyzipay\Model\SubMerchant::create($request, $options);

        return new Response($this, $result);
    }

    public function getSubMerchantExternalId(): string
    {
        return $this->getParameter('subMerchantExternalId');
    }

    public function setSubMerchantExternalId(string $value): static
    {
        return $this->setParameter('subMerchantExternalId', $value);
    }

    public function getGsmNumber(): ?string
    {
        return $this->getParameter('gsmNumber');
    }

    public function setGsmNumber(string $value): static
    {
        return $this->setParameter('gsmNumber', $value);
    }

    public function getAddress(): ?string
    {
        return $this->getParameter('address');
    }

    public function setAddress(string $value): static
    {
        return $this->setParameter('address', $value);
    }

    public function getIban(): ?string
    {
        return $this->getParameter('iban');
    }

    public function setIban(string $value): static
    {
        return $this->setParameter('iban', $value);
    }

    public function getSwiftCode(): ?string
    {
        return $this->getParameter('swiftCode');
    }

    public function setSwiftCode(string $value): static
    {
        return $this->setParameter('swiftCode', $value);
    }

    public function getTaxOffice(): ?string
    {
        return $this->getParameter('taxOffice');
    }

    public function setTaxOffice(string $value): static
    {
        return $this->setParameter('taxOffice', $value);
    }

    public function getContactName(): ?string
    {
        return $this->getParameter('contactName');
    }

    public function setContactName(string $value): static
    {
        return $this->setParameter('contactName', $value);
    }

    public function getContactSurname(): ?string
    {
        return $this->getParameter('contactSurname');
    }

    public function setContactSurname(string $value): static
    {
        return $this->setParameter('contactSurname', $value);
    }

    public function getLegalCompanyTitle(): ?string
    {
        return $this->getParameter('legalCompanyTitle');
    }

    public function setLegalCompanyTitle(string $value): static
    {
        return $this->setParameter('legalCompanyTitle', $value);
    }

    public function getTaxNumber(): ?string
    {
        return $this->getParameter('taxNumber');
    }

    public function setTaxNumber(string $value): static
    {
        return $this->setParameter('taxNumber', $value);
    }

    public function getSubMerchantType(): string
    {
        return $this->getParameter('subMerchantType');
    }

    public function setSubMerchantType(string $value): static
    {
        return $this->setParameter('subMerchantType', $value);
    }

    public function getName(): ?string
    {
        return $this->getParameter('name');
    }

    public function setName(string $value): static
    {
        return $this->setParameter('name', $value);
    }

    public function getPrice(): ?string
    {
        return $this->getParameter('price');
    }

    public function setPrice(string $value): static
    {
        return $this->setParameter('price', $value);
    }
}
