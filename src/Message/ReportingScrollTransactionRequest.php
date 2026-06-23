<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\ReportingScrollTransaction;

class ReportingScrollTransactionRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('transactionDate');

        return [
            'conversationId' => $this->getConversationId(),
            'transactionDate' => $this->getTransactionDate(),
            'lastId' => $this->getParameter('lastId') ?: '',
            'documentScrollVoSortingOrder' => $this->getParameter('documentScrollVoSortingOrder') ?: '',
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\ReportingScrollTransactionRequest();
        $request->setTransactionDate($data['transactionDate']);
        $request->setLastId($data['lastId']);
        $request->setDocumentScrollVoSortingOrder($data['documentScrollVoSortingOrder']);

        $result = ReportingScrollTransaction::create($request, $options);

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

    public function getLastId(): string
    {
        return $this->getParameter('lastId') ?: '';
    }

    public function setLastId(string $value): static
    {
        return $this->setParameter('lastId', $value);
    }

    public function getDocumentScrollVoSortingOrder(): string
    {
        return $this->getParameter('documentScrollVoSortingOrder') ?: '';
    }

    public function setDocumentScrollVoSortingOrder(string $value): static
    {
        return $this->setParameter('documentScrollVoSortingOrder', $value);
    }
}