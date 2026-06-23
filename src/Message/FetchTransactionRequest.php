<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Payment;

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

        $result = Payment::retrieve($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'payment-detail');

        return $response;
    }
}
