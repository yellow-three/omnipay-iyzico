<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Iyzilink\IyziLinkSaveProduct;

class IyziLinkSaveProductRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('name', 'price', 'currencyCode');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'name' => $this->getName(),
            'description' => $this->getProductDescription(),
            'base64EncodedImage' => $this->getBase64EncodedImage(),
            'price' => $this->getPrice(),
            'currencyCode' => $this->getCurrencyCode(),
            'addressIgnorable' => $this->getAddressIgnorable(),
            'soldLimit' => $this->getSoldLimit(),
            'installmentRequested' => $this->getInstallmentRequested(),
            'sourceType' => $this->getSourceType(),
            'stockEnabled' => $this->getStockEnabled(),
            'stockCount' => $this->getStockCount(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Iyzilink\IyziLinkSaveProductRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setName($data['name']);
        $request->setDescription($data['description'] ?? '');
        $request->setBase64EncodedImage($data['base64EncodedImage'] ?? '');
        $request->setPrice($data['price']);
        $request->setCurrency($data['currencyCode']);
        if (isset($data['addressIgnorable'])) {
            $request->setAddressIgnorable($data['addressIgnorable']);
        }
        if (isset($data['soldLimit'])) {
            $request->setSoldLimit($data['soldLimit']);
        }
        if (isset($data['installmentRequested'])) {
            $request->setInstallmentRequest($data['installmentRequested']);
        }
        $request->setSourceType($data['sourceType'] ?? 'WEB');
        if (isset($data['stockEnabled'])) {
            $request->setStockEnabled($data['stockEnabled']);
        }
        if (isset($data['stockCount'])) {
            $request->setStockCount($data['stockCount']);
        }

        $result = IyziLinkSaveProduct::create($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'iyzilink-create');

        return $response;
    }

    public function getName(): string
    {
        return $this->getParameter('name');
    }

    public function setName(string $value): static
    {
        return $this->setParameter('name', $value);
    }

    public function getProductDescription(): string
    {
        return $this->getParameter('description') ?? '';
    }

    public function setProductDescription(string $value): static
    {
        return $this->setParameter('description', $value);
    }

    public function getBase64EncodedImage(): ?string
    {
        return $this->getParameter('base64EncodedImage');
    }

    public function setBase64EncodedImage(string $value): static
    {
        return $this->setParameter('base64EncodedImage', $value);
    }

    public function getPrice(): float
    {
        return (float) $this->getParameter('price');
    }

    public function setPrice(float $value): static
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

    public function getAddressIgnorable(): bool
    {
        return (bool) $this->getParameter('addressIgnorable');
    }

    public function setAddressIgnorable(bool $value): static
    {
        return $this->setParameter('addressIgnorable', $value);
    }

    public function getSoldLimit(): int
    {
        return (int) $this->getParameter('soldLimit');
    }

    public function setSoldLimit(int $value): static
    {
        return $this->setParameter('soldLimit', $value);
    }

    public function getInstallmentRequested(): bool
    {
        return (bool) $this->getParameter('installmentRequested');
    }

    public function setInstallmentRequested(bool $value): static
    {
        return $this->setParameter('installmentRequested', $value);
    }

    public function getSourceType(): string
    {
        return $this->getParameter('sourceType') ?: 'WEB';
    }

    public function setSourceType(string $value): static
    {
        return $this->setParameter('sourceType', $value);
    }

    public function getStockEnabled(): bool
    {
        return (bool) $this->getParameter('stockEnabled');
    }

    public function setStockEnabled(bool $value): static
    {
        return $this->setParameter('stockEnabled', $value);
    }

    public function getStockCount(): int
    {
        return (int) $this->getParameter('stockCount');
    }

    public function setStockCount(int $value): static
    {
        return $this->setParameter('stockCount', $value);
    }
}