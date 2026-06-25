<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Iyzico\Message\Subscription\CreatePricingPlanRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class CreatePricingPlanRequestTest extends TestCase
{
    private CreatePricingPlanRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new CreatePricingPlanRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setName('Premium Plan');
        $this->request->setProductReferenceCode('product_ref_001');
        $this->request->setPrice('49.99');
        $this->request->setCurrencyCode('TRY');
        $this->request->setPaymentInterval('MONTHLY');
        $this->request->setPaymentIntervalCount(1);
        $this->request->setTrialPeriodDays(7);
        $this->request->setPlanPaymentType('RECURRING');
        $this->request->setRecurrenceCount(12);
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('Premium Plan', $data['name']);
        $this->assertSame('product_ref_001', $data['productReferenceCode']);
        $this->assertSame('49.99', $data['price']);
        $this->assertSame('TRY', $data['currencyCode']);
        $this->assertSame('MONTHLY', $data['paymentInterval']);
        $this->assertSame(1, $data['paymentIntervalCount']);
        $this->assertSame(7, $data['trialPeriodDays']);
        $this->assertSame('RECURRING', $data['planPaymentType']);
        $this->assertSame(12, $data['recurrenceCount']);
    }

    public function testGetDataThrowsWhenNameMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The name parameter is required');

        $this->request->getData();
    }

    public function testGetDataThrowsWhenProductReferenceCodeMissing(): void
    {
        $this->request->setName('Premium Plan');

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The productReferenceCode parameter is required');

        $this->request->getData();
    }

    public function testGetDataThrowsWhenPriceMissing(): void
    {
        $this->request->setName('Premium Plan');
        $this->request->setProductReferenceCode('product_ref_001');

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The price parameter is required');

        $this->request->getData();
    }

    public function testGetDataThrowsWhenCurrencyCodeMissing(): void
    {
        $this->request->setName('Premium Plan');
        $this->request->setProductReferenceCode('product_ref_001');
        $this->request->setPrice('49.99');

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The currencyCode parameter is required');

        $this->request->getData();
    }

    public function testGetDataThrowsWhenPaymentIntervalMissing(): void
    {
        $this->request->setName('Premium Plan');
        $this->request->setProductReferenceCode('product_ref_001');
        $this->request->setPrice('49.99');
        $this->request->setCurrencyCode('TRY');

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The paymentInterval parameter is required');

        $this->request->getData();
    }

    public function testGetName(): void
    {
        $this->request->setName('Premium Plan');

        $this->assertSame('Premium Plan', $this->request->getName());
    }

    public function testSetNameReturnsSelf(): void
    {
        $result = $this->request->setName('Premium Plan');

        $this->assertSame($this->request, $result);
    }

    public function testGetProductReferenceCode(): void
    {
        $this->request->setProductReferenceCode('product_ref_001');

        $this->assertSame('product_ref_001', $this->request->getProductReferenceCode());
    }

    public function testSetProductReferenceCodeReturnsSelf(): void
    {
        $result = $this->request->setProductReferenceCode('product_ref_001');

        $this->assertSame($this->request, $result);
    }

    public function testGetPrice(): void
    {
        $this->request->setPrice('49.99');

        $this->assertSame('49.99', $this->request->getPrice());
    }

    public function testSetPriceReturnsSelf(): void
    {
        $result = $this->request->setPrice('49.99');

        $this->assertSame($this->request, $result);
    }

    public function testGetCurrencyCode(): void
    {
        $this->request->setCurrencyCode('TRY');

        $this->assertSame('TRY', $this->request->getCurrencyCode());
    }

    public function testSetCurrencyCodeReturnsSelf(): void
    {
        $result = $this->request->setCurrencyCode('TRY');

        $this->assertSame($this->request, $result);
    }

    public function testGetPaymentInterval(): void
    {
        $this->request->setPaymentInterval('MONTHLY');

        $this->assertSame('MONTHLY', $this->request->getPaymentInterval());
    }

    public function testSetPaymentIntervalReturnsSelf(): void
    {
        $result = $this->request->setPaymentInterval('MONTHLY');

        $this->assertSame($this->request, $result);
    }

    public function testGetPaymentIntervalCount(): void
    {
        $this->request->setPaymentIntervalCount(1);

        $this->assertSame(1, $this->request->getPaymentIntervalCount());
    }

    public function testSetPaymentIntervalCountReturnsSelf(): void
    {
        $result = $this->request->setPaymentIntervalCount(1);

        $this->assertSame($this->request, $result);
    }

    public function testGetTrialPeriodDays(): void
    {
        $this->request->setTrialPeriodDays(7);

        $this->assertSame(7, $this->request->getTrialPeriodDays());
    }

    public function testSetTrialPeriodDaysReturnsSelf(): void
    {
        $result = $this->request->setTrialPeriodDays(7);

        $this->assertSame($this->request, $result);
    }

    public function testGetPlanPaymentType(): void
    {
        $this->request->setPlanPaymentType('RECURRING');

        $this->assertSame('RECURRING', $this->request->getPlanPaymentType());
    }

    public function testSetPlanPaymentTypeReturnsSelf(): void
    {
        $result = $this->request->setPlanPaymentType('RECURRING');

        $this->assertSame($this->request, $result);
    }

    public function testGetRecurrenceCount(): void
    {
        $this->request->setRecurrenceCount(12);

        $this->assertSame(12, $this->request->getRecurrenceCount());
    }

    public function testSetRecurrenceCountReturnsSelf(): void
    {
        $result = $this->request->setRecurrenceCount(12);

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setName('Premium Plan');
        $this->request->setProductReferenceCode('product_ref_001');
        $this->request->setPrice('49.99');
        $this->request->setCurrencyCode('TRY');
        $this->request->setPaymentInterval('MONTHLY');
        $this->request->setPaymentIntervalCount(1);
        $this->request->setTrialPeriodDays(7);
        $this->request->setPlanPaymentType('RECURRING');
        $this->request->setRecurrenceCount(12);
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
                'referenceCode' => 'pricing_plan_ref_001',
                'name' => 'Premium Plan',
                'productReferenceCode' => 'product_ref_001',
                'price' => '49.99',
                'currencyCode' => 'TRY',
                'paymentInterval' => 'MONTHLY',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setName('Premium Plan');
        $this->request->setProductReferenceCode('product_ref_fail');
        $this->request->setPrice('49.99');
        $this->request->setCurrencyCode('TRY');
        $this->request->setPaymentInterval('MONTHLY');
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
                'errorMessage' => 'Pricing plan already exists',
                'errorGroup' => 'PRICING_PLAN_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5001', $response->getCode());
        $this->assertStringContainsString('Pricing plan already exists', $response->getMessage());
    }
}
