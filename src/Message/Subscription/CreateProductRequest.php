<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class CreateProductRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('name');

        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionCreateProductRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setName($data['name']);
        $request->setDescription($data['description']);

        $result = \Iyzipay\Model\Subscription\SubscriptionProduct::create($request, $options);

        return new Response($this, $result);
    }

    public function getName(): ?string
    {
        return $this->getParameter('name');
    }

    public function setName(string $value): static
    {
        return $this->setParameter('name', $value);
    }

}
