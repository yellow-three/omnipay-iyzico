<?php

namespace Omnipay\Iyzico\Message\Marketplace;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class DisapprovePaymentRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('paymentTransactionId');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'paymentTransactionId' => $this->getPaymentTransactionId(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\CreateApprovalRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPaymentTransactionId($data['paymentTransactionId']);

        $result = \Iyzipay\Model\Disapproval::create($request, $options);

        return new Response($this, $result);
    }

    public function getPaymentTransactionId(): string
    {
        return $this->getParameter('paymentTransactionId');
    }

    public function setPaymentTransactionId(string $value): static
    {
        return $this->setParameter('paymentTransactionId', $value);
    }
}
