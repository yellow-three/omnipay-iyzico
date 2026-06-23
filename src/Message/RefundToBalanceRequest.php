<?php

namespace Omnipay\Iyzico\Message;

class RefundToBalanceRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('paymentId');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'paymentId' => $this->getParameter('paymentId'),
            'callbackUrl' => $this->getReturnUrl(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\CreateRefundToBalanceRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPaymentId($data['paymentId']);
        if (!empty($data['callbackUrl'])) {
            $request->setCallbackUrl($data['callbackUrl']);
        }

        $result = \Iyzipay\Model\RefundToBalance::create($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'refund');

        return $response;
    }

    public function getPaymentId(): string
    {
        return $this->getParameter('paymentId');
    }

    public function setPaymentId(string $value): static
    {
        return $this->setParameter('paymentId', $value);
    }
}
