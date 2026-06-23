<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\ReportingPaymentTransaction;

class ReportingPaymentTransactionRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('transactionDate');

        return [
            'conversationId' => $this->getConversationId(),
            'transactionDate' => $this->getTransactionDate(),
            'page' => $this->getPage(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\ReportingPaymentTransactionRequest();
        $request->setConversationId($data['conversationId']);
        $request->setTransactionDate($data['transactionDate']);
        if (!empty($data['page'])) {
            $request->setPage($data['page']);
        }

        $result = ReportingPaymentTransaction::create($request, $options);

        return new Response($this, $result);
    }

    public function getTransactionDate(): string
    {
        return $this->getParameter('transactionDate');
    }

    public function setTransactionDate(string $value): static
    {
        return $this->setParameter('transactionDate', $value);
    }

    public function getPage(): int
    {
        return (int) $this->getParameter('page');
    }

    public function setPage(int $value): static
    {
        return $this->setParameter('page', $value);
    }
}