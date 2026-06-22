<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\BinNumber;

class BinNumberRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('binNumber');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'binNumber' => $this->getBinNumber(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\RetrieveBinNumberRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setBinNumber($data['binNumber']);

        $result = BinNumber::retrieve($request, $options);

        return new Response($this, $result);
    }
}
