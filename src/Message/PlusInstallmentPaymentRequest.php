<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\PlusInstallmentPayment;
use Omnipay\Common\Exception\InvalidRequestException;

class PlusInstallmentPaymentRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('card', 'amount');
        $this->getCard()->validate();

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'price' => $this->getAmount(),
            'paidPrice' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'installment' => $this->getInstallment(),
            'paymentChannel' => $this->getPaymentChannel(),
            'paymentGroup' => $this->getPaymentGroup(),
            'basketId' => $this->getBasketId() ?: uniqid('basket_', true),
            'card' => $this->getCard(),
            'connectorName' => $this->getConnectorName(),
            'plusInstallmentUsage' => $this->getPlusInstallmentUsage() ?: 0,
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();
        $card = $data['card'];

        $request = new \Iyzipay\Request\CreatePlusInstallmentPaymentRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPrice((float) $data['price']);
        $request->setPaidPrice((float) $data['paidPrice']);
        $request->setCurrency($this->mapCurrency($data['currency']));
        $request->setInstallment($data['installment']);
        $request->setPaymentChannel($this->mapPaymentChannel($data['paymentChannel']));
        $request->setPaymentGroup($this->mapPaymentGroup($data['paymentGroup']));
        $request->setBasketId($data['basketId']);
        $request->setConnectorName((string) $data['connectorName']);
        $request->setPlusInstallmentUsage((int) $data['plusInstallmentUsage']);

        $request->setPaymentCard($this->buildPaymentCard($card));
        $request->setBuyer($this->buildBuyer($card));
        $request->setShippingAddress($this->buildShippingAddress($card));
        $request->setBillingAddress($this->buildBillingAddress($card));
        $request->setBasketItems($this->buildBasketItems());

        $result = PlusInstallmentPayment::create($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'non-3ds');

        return $response;
    }

    public function getBasketId(): ?string
    {
        return $this->getParameter('basketId');
    }

    public function setBasketId(string $value): static
    {
        return $this->setParameter('basketId', $value);
    }

    public function getConnectorName(): ?string
    {
        return $this->getParameter('connectorName');
    }

    public function setConnectorName(string $value): static
    {
        return $this->setParameter('connectorName', $value);
    }

    public function getPlusInstallmentUsage(): ?int
    {
        return $this->getParameter('plusInstallmentUsage');
    }

    public function setPlusInstallmentUsage(int $value): static
    {
        return $this->setParameter('plusInstallmentUsage', $value);
    }
}
