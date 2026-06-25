<?php

namespace Omnipay\Iyzico;

use Omnipay\Common\AbstractGateway;

class Gateway extends AbstractGateway
{
    /**
     * setTestMode() çağrıldığında baseUrl buradan türetilir. setBaseUrl() ile elle
     * özel bir URL verirsen (örn. iyzico ileride farklı bir endpoint sağlarsa) o
     * değer kalıcı olur — sonraki bir setTestMode() çağrısı onu tekrar ezer, bu
     * yüzden ikisini aynı anda kullanmak istemiyorsan sadece birini çağır.
     */
    protected array $endpoints = [
        'test' => 'https://sandbox-api.iyzipay.com',
        'live' => 'https://api.iyzipay.com',
    ];

    public function getName(): string
    {
        return 'Iyzico';
    }

    public function getDefaultParameters(): array
    {
        return [
            'apiKey' => '',
            'secretKey' => '',
            'baseUrl' => $this->endpoints['test'],
            // Varsayılan olarak sandbox: yanlışlıkla canlı ortama istek gitmesin.
            'testMode' => true,
            'locale' => 'TR',
            'conversationId' => '',
            'paymentChannel' => 'WEB',
            'paymentGroup' => 'PRODUCT',
            'currency' => 'TRY',
            'installment' => 1,
            'identityNumber' => '',
            'secure3d' => true,
        ];
    }

    public function getTestMode(): bool
    {
        return (bool) $this->getParameter('testMode');
    }

    public function setTestMode($value): static
    {
        $this->setParameter('testMode', (bool) $value);
        $this->setParameter('baseUrl', $value ? $this->endpoints['test'] : $this->endpoints['live']);

        return $this;
    }

    public function getApiKey(): string
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey(string $value): static
    {
        return $this->setParameter('apiKey', $value);
    }

    public function getSecretKey(): string
    {
        return $this->getParameter('secretKey');
    }

    public function setSecretKey(string $value): static
    {
        return $this->setParameter('secretKey', $value);
    }

    public function getBaseUrl(): string
    {
        return $this->getParameter('baseUrl');
    }

    public function setBaseUrl(string $value): static
    {
        return $this->setParameter('baseUrl', $value);
    }

    public function getLocale(): string
    {
        return $this->getParameter('locale');
    }

    public function setLocale(string $value): static
    {
        return $this->setParameter('locale', $value);
    }

    public function getConversationId(): string
    {
        return $this->getParameter('conversationId');
    }

    public function setConversationId(string $value): static
    {
        return $this->setParameter('conversationId', $value);
    }

    public function getPaymentChannel(): string
    {
        return $this->getParameter('paymentChannel');
    }

    public function setPaymentChannel(string $value): static
    {
        return $this->setParameter('paymentChannel', $value);
    }

    public function getPaymentGroup(): string
    {
        return $this->getParameter('paymentGroup');
    }

    public function setPaymentGroup(string $value): static
    {
        return $this->setParameter('paymentGroup', $value);
    }

    public function getInstallment(): int
    {
        return $this->getParameter('installment');
    }

    public function setInstallment(int $value): static
    {
        return $this->setParameter('installment', $value);
    }

    public function getIdentityNumber(): string
    {
        return $this->getParameter('identityNumber');
    }

    public function setIdentityNumber(string $value): static
    {
        return $this->setParameter('identityNumber', $value);
    }

    public function getSecure3d(): bool
    {
        return $this->getParameter('secure3d');
    }

    public function setSecure3d(bool $value): static
    {
        return $this->setParameter('secure3d', $value);
    }

    public function purchase(array $parameters = []): Message\PurchaseRequest
    {
        return $this->createRequest(Message\PurchaseRequest::class, $parameters);
    }

    public function authorize(array $parameters = []): Message\AuthorizeRequest
    {
        return $this->createRequest(Message\AuthorizeRequest::class, $parameters);
    }

    public function capture(array $parameters = []): Message\CaptureRequest
    {
        return $this->createRequest(Message\CaptureRequest::class, $parameters);
    }

    public function refund(array $parameters = []): Message\RefundRequest
    {
        return $this->createRequest(Message\RefundRequest::class, $parameters);
    }

    public function void(array $parameters = []): Message\VoidRequest
    {
        return $this->createRequest(Message\VoidRequest::class, $parameters);
    }

    public function fetchTransaction(array $parameters = []): Message\FetchTransactionRequest
    {
        return $this->createRequest(Message\FetchTransactionRequest::class, $parameters);
    }

    public function fetchBinNumber(array $parameters = []): Message\BinNumberRequest
    {
        return $this->createRequest(Message\BinNumberRequest::class, $parameters);
    }

    public function fetchInstallment(array $parameters = []): Message\InstallmentRequest
    {
        return $this->createRequest(Message\InstallmentRequest::class, $parameters);
    }

    public function checkout(array $parameters = []): Message\CheckoutRequest
    {
        return $this->createRequest(Message\CheckoutRequest::class, $parameters);
    }

    public function checkoutStatus(array $parameters = []): Message\CheckoutStatusRequest
    {
        return $this->createRequest(Message\CheckoutStatusRequest::class, $parameters);
    }

    public function completePurchase(array $parameters = []): Message\CompletePurchaseRequest
    {
        return $this->createRequest(Message\CompletePurchaseRequest::class, $parameters);
    }

    public function acceptNotification(array $parameters = []): Message\AcceptNotificationRequest
    {
        return $this->createRequest(Message\AcceptNotificationRequest::class, $parameters);
    }

    public function payWithIyzico(array $parameters = []): Message\PayWithIyzicoInitializeRequest
    {
        return $this->createRequest(Message\PayWithIyzicoInitializeRequest::class, $parameters);
    }

    public function payWithIyzicoStatus(array $parameters = []): Message\PayWithIyzicoRetrieveRequest
    {
        return $this->createRequest(Message\PayWithIyzicoRetrieveRequest::class, $parameters);
    }

    public function checkoutFormPreAuth(array $parameters = []): Message\CheckoutFormPreAuthRequest
    {
        return $this->createRequest(Message\CheckoutFormPreAuthRequest::class, $parameters);
    }

    public function payWithIyzicoPreAuth(array $parameters = []): Message\PayWithIyzicoPreAuthRequest
    {
        return $this->createRequest(Message\PayWithIyzicoPreAuthRequest::class, $parameters);
    }

    public function basicThreedsPreAuth(array $parameters = []): Message\BasicThreedsPreAuthRequest
    {
        return $this->createRequest(Message\BasicThreedsPreAuthRequest::class, $parameters);
    }

    public function createCard(array $parameters = []): Message\CreateCardRequest
    {
        return $this->createRequest(Message\CreateCardRequest::class, $parameters);
    }

    public function deleteCard(array $parameters = []): Message\DeleteCardRequest
    {
        return $this->createRequest(Message\DeleteCardRequest::class, $parameters);
    }

    public function listCards(array $parameters = []): Message\ListCardsRequest
    {
        return $this->createRequest(Message\ListCardsRequest::class, $parameters);
    }

    public function purchasePlusInstallment(array $parameters = []): Message\PlusInstallmentPaymentRequest
    {
        return $this->createRequest(Message\PlusInstallmentPaymentRequest::class, $parameters);
    }

    public function fetchLoyalty(array $parameters = []): Message\LoyaltyRequest
    {
        return $this->createRequest(Message\LoyaltyRequest::class, $parameters);
    }

    public function refundToBalance(array $parameters = []): Message\RefundToBalanceRequest
    {
        return $this->createRequest(Message\RefundToBalanceRequest::class, $parameters);
    }

    public function settlementToBalance(array $parameters = []): Message\SettlementToBalanceRequest
    {
        return $this->createRequest(Message\SettlementToBalanceRequest::class, $parameters);
    }

    public function bkmInitialize(array $parameters = []): Message\BkmInitializeRequest
    {
        return $this->createRequest(Message\BkmInitializeRequest::class, $parameters);
    }

    public function bkmStatus(array $parameters = []): Message\BkmRetrieveRequest
    {
        return $this->createRequest(Message\BkmRetrieveRequest::class, $parameters);
    }

    public function basicBkmInitialize(array $parameters = []): Message\BasicBkmInitializeRequest
    {
        return $this->createRequest(Message\BasicBkmInitializeRequest::class, $parameters);
    }

    public function iyziLinkSaveProduct(array $parameters = []): Message\IyziLinkSaveProductRequest
    {
        return $this->createRequest(Message\IyziLinkSaveProductRequest::class, $parameters);
    }

    public function iyziLinkRetrieveProduct(array $parameters = []): Message\IyziLinkRetrieveProductRequest
    {
        return $this->createRequest(Message\IyziLinkRetrieveProductRequest::class, $parameters);
    }

    public function iyziLinkRetrieveAllProduct(array $parameters = []): Message\IyziLinkRetrieveAllProductRequest
    {
        return $this->createRequest(Message\IyziLinkRetrieveAllProductRequest::class, $parameters);
    }

    public function iyziLinkDeleteProduct(array $parameters = []): Message\IyziLinkDeleteProductRequest
    {
        return $this->createRequest(Message\IyziLinkDeleteProductRequest::class, $parameters);
    }

    public function iyziLinkUpdateProductStatus(array $parameters = []): Message\IyziLinkUpdateProductStatusRequest
    {
        return $this->createRequest(Message\IyziLinkUpdateProductStatusRequest::class, $parameters);
    }

    public function iyziLinkCreateFastLink(array $parameters = []): Message\IyziLinkCreateFastLinkRequest
    {
        return $this->createRequest(Message\IyziLinkCreateFastLinkRequest::class, $parameters);
    }

    public function iyziLinkSearchMerchantProducts(array $parameters = []): Message\IyziLinkSearchMerchantProductsRequest
    {
        return $this->createRequest(Message\IyziLinkSearchMerchantProductsRequest::class, $parameters);
    }

    public function reportingPaymentDetail(array $parameters = []): Message\ReportingPaymentDetailRequest
    {
        return $this->createRequest(Message\ReportingPaymentDetailRequest::class, $parameters);
    }

    public function reportingPaymentTransaction(array $parameters = []): Message\ReportingPaymentTransactionRequest
    {
        return $this->createRequest(Message\ReportingPaymentTransactionRequest::class, $parameters);
    }

    public function reportingScrollTransaction(array $parameters = []): Message\ReportingScrollTransactionRequest
    {
        return $this->createRequest(Message\ReportingScrollTransactionRequest::class, $parameters);
    }

    public function apmInitialize(array $parameters = []): Message\ApmInitializeRequest
    {
        return $this->createRequest(Message\ApmInitializeRequest::class, $parameters);
    }

    public function apmRetrieve(array $parameters = []): Message\ApmRetrieveRequest
    {
        return $this->createRequest(Message\ApmRetrieveRequest::class, $parameters);
    }

    // Marketplace methods

    public function createSubMerchant(array $parameters = []): Message\Marketplace\CreateSubMerchantRequest
    {
        return $this->createRequest(Message\Marketplace\CreateSubMerchantRequest::class, $parameters);
    }

    public function updateSubMerchant(array $parameters = []): Message\Marketplace\UpdateSubMerchantRequest
    {
        return $this->createRequest(Message\Marketplace\UpdateSubMerchantRequest::class, $parameters);
    }

    public function retrieveSubMerchant(array $parameters = []): Message\Marketplace\RetrieveSubMerchantRequest
    {
        return $this->createRequest(Message\Marketplace\RetrieveSubMerchantRequest::class, $parameters);
    }

    public function approvePayment(array $parameters = []): Message\Marketplace\ApprovePaymentRequest
    {
        return $this->createRequest(Message\Marketplace\ApprovePaymentRequest::class, $parameters);
    }

    public function disapprovePayment(array $parameters = []): Message\Marketplace\DisapprovePaymentRequest
    {
        return $this->createRequest(Message\Marketplace\DisapprovePaymentRequest::class, $parameters);
    }

    public function crossBookingFrom(array $parameters = []): Message\Marketplace\CrossBookingFromRequest
    {
        return $this->createRequest(Message\Marketplace\CrossBookingFromRequest::class, $parameters);
    }

    public function crossBookingTo(array $parameters = []): Message\Marketplace\CrossBookingToRequest
    {
        return $this->createRequest(Message\Marketplace\CrossBookingToRequest::class, $parameters);
    }

    public function updateSubMerchantPaymentItem(array $parameters = []): Message\Marketplace\SubMerchantPaymentItemUpdateRequest
    {
        return $this->createRequest(Message\Marketplace\SubMerchantPaymentItemUpdateRequest::class, $parameters);
    }

    // Subscription methods

    // Products
    public function createSubscriptionProduct(array $parameters = []): Message\Subscription\CreateProductRequest
    {
        return $this->createRequest(Message\Subscription\CreateProductRequest::class, $parameters);
    }

    public function updateSubscriptionProduct(array $parameters = []): Message\Subscription\UpdateProductRequest
    {
        return $this->createRequest(Message\Subscription\UpdateProductRequest::class, $parameters);
    }

    public function retrieveSubscriptionProduct(array $parameters = []): Message\Subscription\RetrieveProductRequest
    {
        return $this->createRequest(Message\Subscription\RetrieveProductRequest::class, $parameters);
    }

    public function deleteSubscriptionProduct(array $parameters = []): Message\Subscription\DeleteProductRequest
    {
        return $this->createRequest(Message\Subscription\DeleteProductRequest::class, $parameters);
    }

    public function listSubscriptionProducts(array $parameters = []): Message\Subscription\ListProductsRequest
    {
        return $this->createRequest(Message\Subscription\ListProductsRequest::class, $parameters);
    }

    // Pricing Plans
    public function createSubscriptionPricingPlan(array $parameters = []): Message\Subscription\CreatePricingPlanRequest
    {
        return $this->createRequest(Message\Subscription\CreatePricingPlanRequest::class, $parameters);
    }

    public function updateSubscriptionPricingPlan(array $parameters = []): Message\Subscription\UpdatePricingPlanRequest
    {
        return $this->createRequest(Message\Subscription\UpdatePricingPlanRequest::class, $parameters);
    }

    public function retrieveSubscriptionPricingPlan(array $parameters = []): Message\Subscription\RetrievePricingPlanRequest
    {
        return $this->createRequest(Message\Subscription\RetrievePricingPlanRequest::class, $parameters);
    }

    public function deleteSubscriptionPricingPlan(array $parameters = []): Message\Subscription\DeletePricingPlanRequest
    {
        return $this->createRequest(Message\Subscription\DeletePricingPlanRequest::class, $parameters);
    }

    public function listSubscriptionPricingPlans(array $parameters = []): Message\Subscription\ListPricingPlanRequest
    {
        return $this->createRequest(Message\Subscription\ListPricingPlanRequest::class, $parameters);
    }

    // Customers
    public function createSubscriptionCustomer(array $parameters = []): Message\Subscription\CreateCustomerRequest
    {
        return $this->createRequest(Message\Subscription\CreateCustomerRequest::class, $parameters);
    }

    public function updateSubscriptionCustomer(array $parameters = []): Message\Subscription\UpdateCustomerRequest
    {
        return $this->createRequest(Message\Subscription\UpdateCustomerRequest::class, $parameters);
    }

    public function retrieveSubscriptionCustomer(array $parameters = []): Message\Subscription\RetrieveCustomerRequest
    {
        return $this->createRequest(Message\Subscription\RetrieveCustomerRequest::class, $parameters);
    }

    public function deleteSubscriptionCustomer(array $parameters = []): Message\Subscription\DeleteCustomerRequest
    {
        return $this->createRequest(Message\Subscription\DeleteCustomerRequest::class, $parameters);
    }

    public function listSubscriptionCustomers(array $parameters = []): Message\Subscription\ListCustomersRequest
    {
        return $this->createRequest(Message\Subscription\ListCustomersRequest::class, $parameters);
    }

    // Subscriptions
    public function createSubscription(array $parameters = []): Message\Subscription\CreateSubscriptionRequest
    {
        return $this->createRequest(Message\Subscription\CreateSubscriptionRequest::class, $parameters);
    }

    public function createSubscriptionWithCustomer(array $parameters = []): Message\Subscription\CreateSubscriptionWithCustomerRequest
    {
        return $this->createRequest(Message\Subscription\CreateSubscriptionWithCustomerRequest::class, $parameters);
    }

    public function createSubscriptionCheckoutForm(array $parameters = []): Message\Subscription\CreateCheckoutFormRequest
    {
        return $this->createRequest(Message\Subscription\CreateCheckoutFormRequest::class, $parameters);
    }

    public function retrieveSubscriptionCheckoutForm(array $parameters = []): Message\Subscription\RetrieveCheckoutFormRequest
    {
        return $this->createRequest(Message\Subscription\RetrieveCheckoutFormRequest::class, $parameters);
    }

    public function retrieveSubscriptionDetails(array $parameters = []): Message\Subscription\DetailsRequest
    {
        return $this->createRequest(Message\Subscription\DetailsRequest::class, $parameters);
    }

    public function activateSubscription(array $parameters = []): Message\Subscription\ActivateRequest
    {
        return $this->createRequest(Message\Subscription\ActivateRequest::class, $parameters);
    }

    public function cancelSubscription(array $parameters = []): Message\Subscription\CancelRequest
    {
        return $this->createRequest(Message\Subscription\CancelRequest::class, $parameters);
    }

    public function retrySubscription(array $parameters = []): Message\Subscription\RetryRequest
    {
        return $this->createRequest(Message\Subscription\RetryRequest::class, $parameters);
    }

    public function upgradeSubscription(array $parameters = []): Message\Subscription\UpgradeRequest
    {
        return $this->createRequest(Message\Subscription\UpgradeRequest::class, $parameters);
    }

    public function listSubscriptions(array $parameters = []): Message\Subscription\ListRequest
    {
        return $this->createRequest(Message\Subscription\ListRequest::class, $parameters);
    }

    public function searchSubscriptions(array $parameters = []): Message\Subscription\SearchRequest
    {
        return $this->createRequest(Message\Subscription\SearchRequest::class, $parameters);
    }

    // Card Update
    public function updateSubscriptionCard(array $parameters = []): Message\Subscription\CardUpdateRequest
    {
        return $this->createRequest(Message\Subscription\CardUpdateRequest::class, $parameters);
    }

    public function updateSubscriptionCardForSubscription(array $parameters = []): Message\Subscription\CardUpdateWithSubscriptionReferenceCodeRequest
    {
        return $this->createRequest(Message\Subscription\CardUpdateWithSubscriptionReferenceCodeRequest::class, $parameters);
    }
}
