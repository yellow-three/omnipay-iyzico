<?php

namespace Omnipay\Iyzico\Message;

class SettlementToBalanceRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('amount');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'subMerchantKey' => $this->getParameter('subMerchantKey'),
            'callbackUrl' => $this->getReturnUrl(),
            'price' => $this->getAmount(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\CreateSettlementToBalanceRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setSubMerchantKey($data['subMerchantKey']);
        if (!empty($data['callbackUrl'])) {
            $request->setCallbackUrl($data['callbackUrl']);
        }
        $request->setPrice($data['price']);

        $result = \Iyzipay\Model\SettlementToBalance::create($request, $options);

        $response = new Response($this, $result);

        return $response;
    }

    public function getSubMerchantKey(): string
    {
        return $this->getParameter('subMerchantKey');
    }

    public function setSubMerchantKey(string $value): static
    {
        return $this->setParameter('subMerchantKey', $value);
    }
}
