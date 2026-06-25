<?php

namespace Omnipay\Iyzico\Message\Marketplace;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class RetrieveSubMerchantRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('subMerchantExternalId');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'subMerchantExternalId' => $this->getSubMerchantExternalId(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\RetrieveSubMerchantRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setSubMerchantExternalId($data['subMerchantExternalId']);

        $result = \Iyzipay\Model\SubMerchant::retrieve($request, $options);

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
}
