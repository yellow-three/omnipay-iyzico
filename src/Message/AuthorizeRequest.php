<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\ThreedsInitialize;
use Iyzipay\Model\PaymentPreAuth;

class AuthorizeRequest extends AbstractRequest
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

            // Same as PurchaseRequest: ThreedsInitialize returns HTML (getHtmlContent()),
            // not a redirect URL or token. Pre-auth via 3DS still goes through the same
            // /payment/3dsecure/initialize endpoint; only the completion step differs
            // (handled later via CompletePurchaseRequest -> ThreedsPayment::create()).
            return new RedirectResponse($this, $result);
        }

        $result = PaymentPreAuth::create($request, $options);

        return new Response($this, $result);
    }
}
