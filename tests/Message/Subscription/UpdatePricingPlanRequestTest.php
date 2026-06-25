<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Iyzico\Message\Subscription\UpdatePricingPlanRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class UpdatePricingPlanRequestTest extends TestCase
{
    private UpdatePricingPlanRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new UpdatePricingPlanRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setPricingPlanReferenceCode('pp_ref_001');
        $this->request->setName('Updated Premium Plan');
        $this->request->setTrialPeriodDays(14);
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('pp_ref_001', $data['pricingPlanReferenceCode']);
        $this->assertSame('Updated Premium Plan', $data['name']);
        $this->assertSame(14, $data['trialPeriodDays']);
    }

    public function testGetDataThrowsWhenPricingPlanReferenceCodeMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The pricingPlanReferenceCode parameter is required');

        $this->request->getData();
    }

    public function testGetPricingPlanReferenceCode(): void
    {
        $this->request->setPricingPlanReferenceCode('pp_ref_001');

        $this->assertSame('pp_ref_001', $this->request->getPricingPlanReferenceCode());
    }

    public function testSetPricingPlanReferenceCodeReturnsSelf(): void
    {
        $result = $this->request->setPricingPlanReferenceCode('pp_ref_001');

        $this->assertSame($this->request, $result);
    }

    public function testGetName(): void
    {
        $this->request->setName('Updated Premium Plan');

        $this->assertSame('Updated Premium Plan', $this->request->getName());
    }

    public function testSetNameReturnsSelf(): void
    {
        $result = $this->request->setName('Updated Premium Plan');

        $this->assertSame($this->request, $result);
    }

    public function testGetTrialPeriodDays(): void
    {
        $this->request->setTrialPeriodDays(14);

        $this->assertSame(14, $this->request->getTrialPeriodDays());
    }

    public function testSetTrialPeriodDaysReturnsSelf(): void
    {
        $result = $this->request->setTrialPeriodDays(14);

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setPricingPlanReferenceCode('pp_ref_001');
        $this->request->setName('Updated Premium Plan');
        $this->request->setTrialPeriodDays(14);
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
                'pricingPlanReferenceCode' => 'pp_ref_001',
                'name' => 'Updated Premium Plan',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setPricingPlanReferenceCode('pp_ref_not_found');
        $this->request->setName('Updated Premium Plan');
        $this->request->setTrialPeriodDays(14);
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
                'errorCode' => '5002',
                'errorMessage' => 'Pricing plan not found',
                'errorGroup' => 'PRICING_PLAN_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5002', $response->getCode());
        $this->assertStringContainsString('Pricing plan not found', $response->getMessage());
    }
}
