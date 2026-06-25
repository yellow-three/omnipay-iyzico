<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class RetrieveCheckoutFormRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('token');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'token' => $this->getToken(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\RetrieveSubscriptionCreateCheckoutFormRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setCheckoutFormToken($data['token']);

        $result = \Iyzipay\Model\Subscription\RetrieveSubscriptionCheckoutForm::retrieve($request, $options);

        return new Response($this, $result);
    }

}
