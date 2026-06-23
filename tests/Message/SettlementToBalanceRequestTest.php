<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\SettlementToBalanceRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class SettlementToBalanceRequestTest extends TestCase
{
    private SettlementToBalanceRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new SettlementToBalanceRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setSubMerchantKey('sub_merchant_key');
        $this->request->setAmount('100.00');
        $this->request->setConversationId('conv_123');
        $this->request->setLocale('TR');
        $this->request->setReturnUrl('https://example.com/callback');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('sub_merchant_key', $data['subMerchantKey']);
        $this->assertSame('https://example.com/callback', $data['callbackUrl']);
        $this->assertSame('100.00', $data['price']);
    }

    public function testGetDataThrowsWhenAmountMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The amount parameter is required');

        $this->request->getData();
    }

    public function testGetSubMerchantKey(): void
    {
        $this->request->setSubMerchantKey('sub_key_456');

        $this->assertSame('sub_key_456', $this->request->getSubMerchantKey());
    }

    public function testSetSubMerchantKeyReturnsSelf(): void
    {
        $result = $this->request->setSubMerchantKey('sub_key_456');

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setSubMerchantKey('sub_merchant_key');
        $this->request->setAmount('100.00');
        $this->request->setConversationId('conv_123');
        $this->request->setLocale('TR');
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
        $this->request->setSubMerchantKey('sub_merchant_key');
        $this->request->setAmount('100.00');
        $this->request->setConversationId('conv_fail');
        $this->request->setLocale('TR');
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
                'errorMessage' => 'Settlement to balance failed',
                'errorGroup' => 'SETTLEMENT_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5002', $response->getCode());
        $this->assertStringContainsString('Settlement to balance failed', $response->getMessage());
    }
}
