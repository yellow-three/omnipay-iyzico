<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\PaymentAuth;

class CompletePurchaseRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('token');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'paymentId' => $this->getPaymentId(),
            'token' => $this->getToken(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\CreatePaymentRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);

        if (!empty($data['paymentId'])) {
            $request->setPaymentId($data['paymentId']);
        }

        $result = PaymentAuth::create($request, $options);

        return new Response($this, $result);
    }

    public function getToken(): string
    {
        return $this->getParameter('token');
    }

    public function setToken(string $value): static
    {
        return $this->setParameter('token', $value);
    }
}
