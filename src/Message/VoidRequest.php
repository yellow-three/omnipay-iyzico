<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Cancel;

class VoidRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('paymentId', 'conversationId');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'paymentId' => $this->getPaymentId(),
            'reason' => $this->getParameter('reason') ?? 'buyer request',
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\CreateCancelRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPaymentId($data['paymentId']);
        $request->setReason($data['reason']);

        $result = Cancel::create($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'refund');

        return $response;
    }

    public function getReason(): string
    {
        return $this->getParameter('reason');
    }

    public function setReason(string $value): static
    {
        return $this->setParameter('reason', $value);
    }
}
