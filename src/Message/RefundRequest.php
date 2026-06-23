<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Refund;
use Iyzipay\Model\RefundReason;

class RefundRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('paymentTransactionId', 'conversationId', 'amount');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'paymentTransactionId' => $this->getParameter('paymentTransactionId'),
            'price' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'reason' => $this->getParameter('reason') ?? RefundReason::BUYER_REQUEST,
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\CreateRefundRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPaymentTransactionId($data['paymentTransactionId']);
        $request->setPrice($data['price']);
        $request->setCurrency($this->mapCurrency($data['currency']));
        $request->setReason($data['reason']);

        $result = Refund::create($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'refund');

        return $response;
    }

    public function getPaymentTransactionId(): string
    {
        return $this->getParameter('paymentTransactionId');
    }

    public function setPaymentTransactionId(string $value): static
    {
        return $this->setParameter('paymentTransactionId', $value);
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
