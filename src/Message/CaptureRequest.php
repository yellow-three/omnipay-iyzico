<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\PaymentCompletion;

class CaptureRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('paymentId', 'conversationId');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'paymentId' => $this->getPaymentId(),
            'paidPrice' => $this->getAmount(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\CreatePaymentCompletionRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPaymentId($data['paymentId']);
        $request->setPaidPrice($data['paidPrice']);

        $result = PaymentCompletion::create($request, $options);

        return new Response($this, $result);
    }
}
