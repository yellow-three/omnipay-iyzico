<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\InstallmentInfo;

class InstallmentRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('binNumber');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'binNumber' => $this->getBinNumber(),
            'price' => $this->getAmount(),
            'currency' => $this->getCurrency(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setBinNumber($data['binNumber']);

        if (!empty($data['price'])) {
            $request->setPrice($data['price']);
        }

        if (!empty($data['currency'])) {
            $request->setCurrency($this->mapCurrency($data['currency']));
        }

        $result = InstallmentInfo::retrieve($request, $options);

        return new Response($this, $result);
    }
}
