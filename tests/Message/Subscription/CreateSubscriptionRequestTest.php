<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Subscription\CreateSubscriptionRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class CreateSubscriptionRequestTest extends TestCase
{
    private CreateSubscriptionRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new CreateSubscriptionRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setPricingPlanReferenceCode('pp_001');
        $this->request->setSubscriptionInitialStatus('ACTIVE');
        $this->request->setCardNumber('4111111111111111');
        $this->request->setCardHolderName('John Doe');
        $this->request->setExpireMonth('12');
        $this->request->setExpireYear('2030');
        $this->request->setCvc('123');
        $this->request->setCardName('My Card');
        $this->request->setCustomerEmail('john@example.com');
        $this->request->setCustomerGsmNumber('+905551112233');
        $this->request->setCustomerName('John');
        $this->request->setCustomerSurname('Doe');
        $this->request->setCustomerIdentityNumber('12345678901');
        $this->request->setCustomerBillingAddress('Istanbul, Turkey');
        $this->request->setCustomerBillingCity('Istanbul');
        $this->request->setCustomerBillingCountry('Turkey');
        $this->request->setCustomerBillingZipCode('34732');
        $this->request->setCustomerBillingContactName('John Doe');
        $this->request->setCustomerShippingAddress('Ankara, Turkey');
        $this->request->setCustomerShippingCity('Ankara');
        $this->request->setCustomerShippingCountry('Turkey');
        $this->request->setCustomerShippingZipCode('06100');
        $this->request->setCustomerShippingContactName('John Doe');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('pp_001', $data['pricingPlanReferenceCode']);
        $this->assertSame('ACTIVE', $data['subscriptionInitialStatus']);
        $this->assertSame('4111111111111111', $data['cardNumber']);
        $this->assertSame('John Doe', $data['cardHolderName']);
        $this->assertSame('12', $data['expireMonth']);
        $this->assertSame('2030', $data['expireYear']);
        $this->assertSame('123', $data['cvc']);
        $this->assertSame('My Card', $data['cardName']);
        $this->assertSame('john@example.com', $data['customerEmail']);
        $this->assertSame('+905551112233', $data['customerGsmNumber']);
        $this->assertSame('John', $data['customerName']);
        $this->assertSame('Doe', $data['customerSurname']);
        $this->assertSame('12345678901', $data['customerIdentityNumber']);
        $this->assertSame('Istanbul, Turkey', $data['customerBillingAddress']);
        $this->assertSame('Istanbul', $data['customerBillingCity']);
        $this->assertSame('Turkey', $data['customerBillingCountry']);
        $this->assertSame('34732', $data['customerBillingZipCode']);
        $this->assertSame('John Doe', $data['customerBillingContactName']);
        $this->assertSame('Ankara, Turkey', $data['customerShippingAddress']);
        $this->assertSame('Ankara', $data['customerShippingCity']);
        $this->assertSame('Turkey', $data['customerShippingCountry']);
        $this->assertSame('06100', $data['customerShippingZipCode']);
        $this->assertSame('John Doe', $data['customerShippingContactName']);
    }

    public function testGetDataThrowsWhenPricingPlanReferenceCodeMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The pricingPlanReferenceCode parameter is required');

        $this->request->getData();
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setPricingPlanReferenceCode('pp_001');
        $this->request->setSubscriptionInitialStatus('ACTIVE');
        $this->request->setCardNumber('4111111111111111');
        $this->request->setCardHolderName('John Doe');
        $this->request->setExpireMonth('12');
        $this->request->setExpireYear('2030');
        $this->request->setCvc('123');
        $this->request->setCardName('My Card');
        $this->request->setCustomerEmail('john@example.com');
        $this->request->setCustomerGsmNumber('+905551112233');
        $this->request->setCustomerName('John');
        $this->request->setCustomerSurname('Doe');
        $this->request->setCustomerIdentityNumber('12345678901');
        $this->request->setCustomerBillingAddress('Istanbul, Turkey');
        $this->request->setCustomerBillingCity('Istanbul');
        $this->request->setCustomerBillingCountry('Turkey');
        $this->request->setCustomerBillingZipCode('34732');
        $this->request->setCustomerBillingContactName('John Doe');
        $this->request->setCustomerShippingAddress('Ankara, Turkey');
        $this->request->setCustomerShippingCity('Ankara');
        $this->request->setCustomerShippingCountry('Turkey');
        $this->request->setCustomerShippingZipCode('06100');
        $this->request->setCustomerShippingContactName('John Doe');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'success',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_123',
                'referenceCode' => 'sub_ref_001',
                'subscriptionStatus' => 'ACTIVE',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setPricingPlanReferenceCode('pp_fail');
        $this->request->setSubscriptionInitialStatus('ACTIVE');
        $this->request->setCardNumber('4111111111111111');
        $this->request->setCardHolderName('John Doe');
        $this->request->setExpireMonth('12');
        $this->request->setExpireYear('2030');
        $this->request->setCvc('123');
        $this->request->setCardName('My Card');
        $this->request->setCustomerEmail('john@example.com');
        $this->request->setCustomerGsmNumber('+905551112233');
        $this->request->setCustomerName('John');
        $this->request->setCustomerSurname('Doe');
        $this->request->setCustomerIdentityNumber('12345678901');
        $this->request->setCustomerBillingAddress('Istanbul, Turkey');
        $this->request->setCustomerBillingCity('Istanbul');
        $this->request->setCustomerBillingCountry('Turkey');
        $this->request->setCustomerBillingZipCode('34732');
        $this->request->setCustomerBillingContactName('John Doe');
        $this->request->setCustomerShippingAddress('Ankara, Turkey');
        $this->request->setCustomerShippingCity('Ankara');
        $this->request->setCustomerShippingCountry('Turkey');
        $this->request->setCustomerShippingZipCode('06100');
        $this->request->setCustomerShippingContactName('John Doe');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_fail');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '5001',
                'errorMessage' => 'Pricing plan not found',
                'errorGroup' => 'SUBSCRIPTION_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5001', $response->getCode());
        $this->assertStringContainsString('Pricing plan not found', $response->getMessage());
    }
}
