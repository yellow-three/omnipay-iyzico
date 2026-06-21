<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Currency as IyzicoCurrency;
use Iyzipay\Model\Payment;
use Iyzipay\Model\ThreedsInitialize;
use Omnipay\Common\Exception\InvalidRequestException;

class PurchaseRequest extends AbstractRequest
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
            'callbackUrl' => $this->getReturnUrl(),
            'secure3d' => $this->getSecure3d(),
            'card' => $this->getCard(),
            'clientIp' => $this->getClientIp(),
        ];
    }

    public function sendData($data): Response|RedirectResponse
    {
        $options = $this->createIyzicoOptions();
        $card = $data['card'];

        $request = new \Iyzipay\Request\CreatePaymentRequest();
        $request->setLocale($this->mapLocale($data['locale']));
        $request->setConversationId($data['conversationId']);
        $request->setPrice($data['price']);
        $request->setPaidPrice($data['paidPrice']);
        $request->setCurrency($this->mapCurrency($data['currency']));
        $request->setInstallment($data['installment']);
        $request->setPaymentChannel($this->mapPaymentChannel($data['paymentChannel']));
        $request->setPaymentGroup($this->mapPaymentGroup($data['paymentGroup']));

        if ($data['secure3d'] && !empty($data['callbackUrl'])) {
            $request->setCallbackUrl($data['callbackUrl']);
        }

        $request->setPaymentCard($this->buildPaymentCard($card));
        $request->setBuyer($this->buildBuyer($card));
        $request->setShippingAddress($this->buildShippingAddress($card));
        $request->setBillingAddress($this->buildBillingAddress($card));
        $request->setBasketItems($this->buildBasketItems());

        if ($data['secure3d']) {
            $result = ThreedsInitialize::create($request, $options);

            // ThreedsInitialize never returns a redirect URL or token — it returns an
            // HTML form (getHtmlContent()) that must be rendered directly in the browser,
            // which auto-submits to the issuing bank's 3DS page. Check
            // $response->getHtmlContent() before falling back to getRedirectUrl().
            return new RedirectResponse($this, $result);
        }

        $result = Payment::create($request, $options);

        return new Response($this, $result);
    }
}
