<?php

namespace Omnipay\Iyzico\Message\Marketplace;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class SubMerchantPaymentItemUpdateRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('paymentTransactionId', 'subMerchantKey', 'subMerchantPrice');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'paymentTransactionId' => $this->getPaymentTransactionId(),
            'subMerchantKey' => $this->getSubMerchantKey(),
            'subMerchantPrice' => $this->getSubMerchantPrice(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\SubMerchantPaymentItemUpdateRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPaymentTransactionId($data['paymentTransactionId']);
        $request->setSubMerchantKey($data['subMerchantKey']);
        $request->setSubMerchantPrice($data['subMerchantPrice']);

        $result = \Iyzipay\Model\SubMerchantPaymentItemUpdate::create($request, $options);

        return new Response($this, $result);
    }

    public function getPaymentTransactionId(): string
    {
        return $this->getParameter('paymentTransactionId');
    }

    public function setPaymentTransactionId(string $value): static
    {
        return $this->setParameter('paymentTransactionId', $value);
    }

    public function getSubMerchantKey(): string
    {
        return $this->getParameter('subMerchantKey');
    }

    public function setSubMerchantKey(string $value): static
    {
        return $this->setParameter('subMerchantKey', $value);
    }

    public function getSubMerchantPrice(): string
    {
        return $this->getParameter('subMerchantPrice');
    }

    public function setSubMerchantPrice(string $value): static
    {
        return $this->setParameter('subMerchantPrice', $value);
    }
}
