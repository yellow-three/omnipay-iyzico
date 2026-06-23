<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Iyzilink\IyziLinkSearchMerchantProducts;

class IyziLinkSearchMerchantProductsRequest extends AbstractRequest
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

        $request = new \Iyzipay\Request\Iyzilink\IyziLinkSearchMerchantProductsRequest();
        if (!empty($data['page'])) {
            $request->setPage($data['page']);
        }
        if (!empty($data['count'])) {
            $request->setCount($data['count']);
        }

        $result = IyziLinkSearchMerchantProducts::create($request, $options);

        return new Response($this, $result);
    }

    public function getPage(): int
    {
        return (int) $this->getParameter('page');
    }

    public function setPage(int $value): static
    {
        return $this->setParameter('page', $value);
    }

    public function getCount(): int
    {
        return (int) $this->getParameter('count');
    }

    public function setCount(int $value): static
    {
        return $this->setParameter('count', $value);
    }
}