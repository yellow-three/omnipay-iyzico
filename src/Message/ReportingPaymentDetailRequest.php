<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\ReportingPaymentDetail;

class ReportingPaymentDetailRequest extends AbstractRequest
{
    public function getData(): array
    {
        return [
            'conversationId' => $this->getConversationId(),
            'paymentId' => $this->getPaymentId(),
            'paymentConversationId' => $this->getParameter('paymentConversationId'),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\ReportingPaymentDetailRequest();
        $request->setConversationId($data['conversationId']);
        if (!empty($data['paymentId'])) {
            $request->setPaymentId($data['paymentId']);
        }
        if (!empty($data['paymentConversationId'])) {
            $request->setPaymentConversationId($data['paymentConversationId']);
        }

        $result = ReportingPaymentDetail::create($request, $options);

        return new Response($this, $result);
    }

    public function getPaymentId(): string
    {
        return $this->getParameter('paymentId') ?? '';
    }

    public function setPaymentId(string $value): static
    {
        return $this->setParameter('paymentId', $value);
    }

    public function getPaymentConversationId(): string
    {
        return $this->getParameter('paymentConversationId');
    }

    public function setPaymentConversationId(string $value): static
    {
        return $this->setParameter('paymentConversationId', $value);
    }
}