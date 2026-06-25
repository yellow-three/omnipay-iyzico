<?php

namespace Omnipay\Iyzico\Message\Subscription;

use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Iyzico\Message\Response;

class ListRequest extends AbstractRequest
{
    public function getData(): array
    {
        return [
            'locale' => $this->getLocale(),
            'conversationId' => $this->getConversationId(),
            'page' => $this->getPage(),
            'count' => $this->getCount(),
            'subscriptionReferenceCode' => $this->getSubscriptionReferenceCode(),
            'subscriptionStatus' => $this->getSubscriptionStatus(),
            'customerReferenceCode' => $this->getCustomerReferenceCode(),
            'parentReferenceCode' => $this->getParentReferenceCode(),
            'pricingPlanReferenceCode' => $this->getPricingPlanReferenceCode(),
            'startDate' => $this->getStartDate(),
            'endDate' => $this->getEndDate(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\Subscription\SubscriptionListRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);

        if ($data['page'] !== null) {
            $request->setPage((int) $data['page']);
        }
        if ($data['count'] !== null) {
            $request->setCount((int) $data['count']);
        }
        if ($data['subscriptionReferenceCode'] !== null) {
            $request->setSubscriptionReferenceCode($data['subscriptionReferenceCode']);
        }
        if ($data['subscriptionStatus'] !== null) {
            $request->setSubscriptionStatus($data['subscriptionStatus']);
        }
        if ($data['customerReferenceCode'] !== null) {
            $request->setCustomerReferenceCode($data['customerReferenceCode']);
        }
        if ($data['parentReferenceCode'] !== null) {
            $request->setParentReferenceCode($data['parentReferenceCode']);
        }
        if ($data['pricingPlanReferenceCode'] !== null) {
            $request->setPricingPlanReferenceCode($data['pricingPlanReferenceCode']);
        }
        if ($data['startDate'] !== null) {
            $request->setStartDate($data['startDate']);
        }
        if ($data['endDate'] !== null) {
            $request->setEndDate($data['endDate']);
        }

        $result = \Iyzipay\Model\Subscription\SubscriptionList::create($request, $options);

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

    public function getSubscriptionReferenceCode(): ?string
    {
        return $this->getParameter('subscriptionReferenceCode');
    }

    public function setSubscriptionReferenceCode(string $value): static
    {
        return $this->setParameter('subscriptionReferenceCode', $value);
    }

    public function getSubscriptionStatus(): ?string
    {
        return $this->getParameter('subscriptionStatus');
    }

    public function setSubscriptionStatus(string $value): static
    {
        return $this->setParameter('subscriptionStatus', $value);
    }

    public function getCustomerReferenceCode(): ?string
    {
        return $this->getParameter('customerReferenceCode');
    }

    public function setCustomerReferenceCode(string $value): static
    {
        return $this->setParameter('customerReferenceCode', $value);
    }

    public function getParentReferenceCode(): ?string
    {
        return $this->getParameter('parentReferenceCode');
    }

    public function setParentReferenceCode(string $value): static
    {
        return $this->setParameter('parentReferenceCode', $value);
    }

    public function getPricingPlanReferenceCode(): ?string
    {
        return $this->getParameter('pricingPlanReferenceCode');
    }

    public function setPricingPlanReferenceCode(string $value): static
    {
        return $this->setParameter('pricingPlanReferenceCode', $value);
    }

    public function getStartDate(): ?string
    {
        return $this->getParameter('startDate');
    }

    public function setStartDate(string $value): static
    {
        return $this->setParameter('startDate', $value);
    }

    public function getEndDate(): ?string
    {
        return $this->getParameter('endDate');
    }

    public function setEndDate(string $value): static
    {
        return $this->setParameter('endDate', $value);
    }
}
