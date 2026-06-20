<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\RetrievePayment;

class FetchTransactionRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('paymentId', 'conversationId');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'paymentId' => $this->getPaymentId(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\RetrievePaymentRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPaymentId($data['paymentId']);

        $result = RetrievePayment::create($request, $options);

        return new Response($this, $result);
    }
}
