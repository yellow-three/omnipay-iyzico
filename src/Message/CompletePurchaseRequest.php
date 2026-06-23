<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\ThreedsPayment;

/**
 * Completes a 3D Secure payment started by PurchaseRequest/AuthorizeRequest.
 *
 * After the buyer finishes the bank's 3DS challenge, iyzico POSTs to your callbackUrl
 * with (at least) paymentId, conversationId, conversationData and mdStatus. Pass those
 * straight through here — this calls /payment/3dsecure/auth, which is the only way to
 * actually finalize a payment that was initialized via ThreedsInitialize.
 *
 * This is unrelated to the Checkout Form flow's token-based completion — that's handled
 * by CheckoutStatusRequest instead.
 */
class CompletePurchaseRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('paymentId', 'conversationData');

        return [
            'conversationId' => $this->getConversationId(),
            'paymentId' => $this->getPaymentId(),
            'conversationData' => $this->getConversationData(),
        ];
    }

    public function sendData($data): Response
    {
        $options = $this->createIyzicoOptions();

        $request = new \Iyzipay\Request\CreateThreedsPaymentRequest();
        $request->setConversationId($data['conversationId']);
        $request->setPaymentId($data['paymentId']);
        $request->setConversationData($data['conversationData']);

        $result = ThreedsPayment::create($request, $options);

        $response = new Response($this, $result);
        $response->applySignature($this->getSecretKey(), '3ds-auth');

        return $response;
    }

    public function getConversationData(): string
    {
        return $this->getParameter('conversationData');
    }

    public function setConversationData(string $value): static
    {
        return $this->setParameter('conversationData', $value);
    }
}
