<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Card;

class CreateCardRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('card', 'email', 'cardUserKey');
        $this->getCard()->validate();

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'email' => $this->getEmail(),
            'cardUserKey' => $this->getCardUserKey(),
            'externalId' => $this->getParameter('externalId') ?? '',
            'card' => $this->getCard(),
            'cardAlias' => $this->getParameter('cardAlias') ?? '',
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();
        $card = $data['card'];

        $paymentCard = $this->buildPaymentCard($card);
        $paymentCard->setRegisterCard(1);

        if (!empty($data['cardAlias'])) {
            $paymentCard->setCardAlias($data['cardAlias']);
        }

        $request = new \Iyzipay\Request\CreateCardRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setEmail($data['email']);
        $request->setCardUserKey($data['cardUserKey']);
        $request->setExternalId($data['externalId']);
        $request->setCard($paymentCard);

        $result = Card::create($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), 'create-card');

        return $response;
    }

    public function getExternalId(): string
    {
        return $this->getParameter('externalId');
    }

    public function setExternalId(string $value): static
    {
        return $this->setParameter('externalId', $value);
    }

    public function getCardAlias(): string
    {
        return $this->getParameter('cardAlias');
    }

    public function setCardAlias(string $value): static
    {
        return $this->setParameter('cardAlias', $value);
    }
}
