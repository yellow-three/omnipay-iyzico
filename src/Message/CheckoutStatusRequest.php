<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\CheckoutForm;

class CheckoutStatusRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('token');

        return [
            'token' => $this->getToken(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        // RetrieveCheckoutFormRequest only accepts a token — no locale/conversationId.
        $request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
        $request->setToken($data['token']);

        $result = CheckoutForm::retrieve($request, $options);

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
