<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class ListPricingPlanRequest extends AbstractRequest
{
    public function getData(): array
    {
        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'page' => $this->getPage(),
            'count' => $this->getCount(),
            'productReferenceCode' => $this->getProductReferenceCode(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionListPricingPlanRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPage($data['page']);
        $request->setCount($data['count']);
        $request->setProductReferenceCode($data['productReferenceCode']);

        $result = \Iyzipay\Model\Subscription\RetrieveList::pricingPlan($request, $options);

        return new Response($this, $result);
    }

    public function getPage(): ?int
    {
        return $this->getParameter('page');
    }

    public function setPage(int $value): static
    {
        return $this->setParameter('page', $value);
    }

    public function getCount(): ?int
    {
        return $this->getParameter('count');
    }

    public function setCount(int $value): static
    {
        return $this->setParameter('count', $value);
    }

    public function getProductReferenceCode(): ?string
    {
        return $this->getParameter('productReferenceCode');
    }

    public function setProductReferenceCode(string $value): static
    {
        return $this->setParameter('productReferenceCode', $value);
    }
}
