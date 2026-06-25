<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Subscription\DetailsRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class DetailsRequestTest extends TestCase
{
    private DetailsRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new DetailsRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setSubscriptionReferenceCode('sub_ref_001');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('sub_ref_001', $data['subscriptionReferenceCode']);
    }

    public function testGetDataThrowsWhenSubscriptionReferenceCodeMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The subscriptionReferenceCode parameter is required');

        $this->request->getData();
    }

    public function testGetSubscriptionReferenceCode(): void
    {
        $this->request->setSubscriptionReferenceCode('sub_ref_002');

        $this->assertSame('sub_ref_002', $this->request->getSubscriptionReferenceCode());
    }

    public function testSetSubscriptionReferenceCodeReturnsSelf(): void
    {
        $result = $this->request->setSubscriptionReferenceCode('sub_ref_002');

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setSubscriptionReferenceCode('sub_ref_001');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('getV2')
            ->willReturn(json_encode([
                'status' => 'success',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_123',
                'referenceCode' => 'sub_ref_001',
                'subscriptionStatus' => 'ACTIVE',
                'customerEmail' => 'john@example.com',
                'pricingPlanReferenceCode' => 'pp_001',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setSubscriptionReferenceCode('sub_ref_not_found');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_fail');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('getV2')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '5005',
                'errorMessage' => 'Subscription not found',
                'errorGroup' => 'SUBSCRIPTION_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5005', $response->getCode());
        $this->assertStringContainsString('Subscription not found', $response->getMessage());
    }
}
