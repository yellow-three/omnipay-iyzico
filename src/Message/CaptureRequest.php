<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\PaymentPostAuth;

class CaptureRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('paymentId', 'amount');

        return [
            'paymentId' => $this->getPaymentId(),
            'paidPrice' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'clientIp' => $this->getClientIp(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        // /payment/postauth only accepts paymentId, paidPrice, ip and currency —
        // it has no locale/conversationId fields (unlike most other iyzico requests).
        $request = new \Iyzipay\Request\CreatePaymentPostAuthRequest();
        $request->setPaymentId($data['paymentId']);
        $request->setPaidPrice($data['paidPrice']);
        $request->setCurrency($this->mapCurrency($data['currency']));
        $request->setIp($data['clientIp'] ?? '127.0.0.1');

        $result = PaymentPostAuth::create($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'postauth');

        return $response;
    }
}
