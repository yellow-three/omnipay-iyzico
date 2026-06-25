<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class ListCustomersRequest extends AbstractRequest
{
    public function getData(): array
    {
        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'page' => $this->getPage(),
            'count' => $this->getCount(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionListCustomersRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPage($data['page']);
        $request->setCount($data['count']);

        $result = \Iyzipay\Model\Subscription\RetrieveList::customers($request, $options);

        return new Response($this, $result);
    }

    public function getPage(): ?int
    {
        return $this->getParameter('page') ? (int) $this->getParameter('page') : null;
    }

    public function setPage(int $value): static
    {
        return $this->setParameter('page', $value);
    }

    public function getCount(): ?int
    {
        return $this->getParameter('count') ? (int) $this->getParameter('count') : null;
    }

    public function setCount(int $value): static
    {
        return $this->setParameter('count', $value);
    }
}
