<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\BkmRetrieveRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class BkmRetrieveRequestTest extends TestCase
{
    private BkmRetrieveRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new BkmRetrieveRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsAllParameters(): void
    {
        $this->request->setToken('token_123');
        $this->request->setConversationId('conv_123');
        $this->request->setLocale('TR');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('token_123', $data['token']);
    }

    public function testGetDataThrowsWhenTokenMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The token parameter is required');

        $this->request->getData();
    }

    public function testGetDataAutoGeneratesConversationId(): void
    {
        $this->request->setToken('token_123');
        $this->request->setLocale('EN');

        $data = $this->request->getData();

        $this->assertArrayHasKey('conversationId', $data);
        $this->assertStringStartsWith('txn_', $data['conversationId']);
    }

    public function testGetToken(): void
    {
        $this->request->setToken('token_456');

        $this->assertSame('token_456', $this->request->getToken());
    }

    public function testSetTokenReturnsSelf(): void
    {
        $result = $this->request->setToken('token_456');

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setToken('token_123');
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
                'token' => 'token_123',
                'paymentId' => 'pay_bkm_123',
                'paymentStatus' => 'SUCCESS',
                'basketId' => 'order_123',
                'currency' => 'TRY',
                'paidPrice' => '100.00',
                'price' => '100.00',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
        $this->assertSame('token_123', $response->getToken());
        $this->assertSame('pay_bkm_123', $response->getPaymentId());
        $this->assertSame('SUCCESS', $response->getPaymentStatus());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setToken('token_fail');
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
                'errorCode' => '5001',
                'errorMessage' => 'BKM not found',
                'errorGroup' => 'NOT_FOUND',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5001', $response->getCode());
        $this->assertStringContainsString('BKM not found', $response->getMessage());
    }
}