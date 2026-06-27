<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Apm;

class ApmRetrieveRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('paymentId');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'paymentId' => $this->getPaymentId(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\RetrieveApmRequest();
        $request->setPaymentId($data['paymentId']);

        $result = Apm::retrieve($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'apm');

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