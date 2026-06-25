<?php

namespace Omnipay\Iyzico\Tests\Message\Marketplace;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Marketplace\CrossBookingFromRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class CrossBookingFromRequestTest extends TestCase
{
    private CrossBookingFromRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new CrossBookingFromRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setSubMerchantKey('sub_merchant_key_from');
        $this->request->setPrice('50.00');
        $this->request->setReason('Commission transfer');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('sub_merchant_key_from', $data['subMerchantKey']);
        $this->assertSame('50.00', $data['price']);
        $this->assertSame('Commission transfer', $data['reason']);
    }

    public function testGetDataThrowsWhenSubMerchantKeyMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The subMerchantKey parameter is required');

        $this->request->getData();
    }

    public function testGetSubMerchantKey(): void
    {
        $this->request->setSubMerchantKey('sub_merchant_key_from');

        $this->assertSame('sub_merchant_key_from', $this->request->getSubMerchantKey());
    }

    public function testSetSubMerchantKeyReturnsSelf(): void
    {
        $result = $this->request->setSubMerchantKey('sub_merchant_key_from');

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setSubMerchantKey('sub_merchant_key_from');
        $this->request->setPrice('50.00');
        $this->request->setReason('Commission transfer');
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
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setSubMerchantKey('sub_merchant_key_from_fail');
        $this->request->setPrice('50.00');
        $this->request->setReason('Commission transfer');
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
                'errorCode' => '5006',
                'errorMessage' => 'Cross booking from sub merchant failed',
                'errorGroup' => 'CROSS_BOOKING_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5006', $response->getCode());
        $this->assertStringContainsString('Cross booking from sub merchant failed', $response->getMessage());
    }
}
