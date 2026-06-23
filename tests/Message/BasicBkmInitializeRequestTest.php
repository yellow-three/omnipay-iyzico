<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\BasicBkmInitializeRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class BasicBkmInitializeRequestTest extends TestCase
{
    private BasicBkmInitializeRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new BasicBkmInitializeRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
        parent::tearDown();
    }

    public function testGetDataReturnsAllParameters(): void
    {
        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setBasketId('order_123');
        $this->request->setPaymentGroup('PRODUCT');
        $this->request->setReturnUrl('https://example.com/callback');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('100.00', $data['price']);
        $this->assertSame('100.00', $data['paidPrice']);
        $this->assertSame('TRY', $data['currency']);
        $this->assertSame('order_123', $data['basketId']);
        $this->assertSame('PRODUCT', $data['paymentGroup']);
        $this->assertSame('https://example.com/callback', $data['callbackUrl']);
    }

    public function testGetDataThrowsWhenAmountMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataGeneratesBasketIdWhenNotSet(): void
    {
        $this->request->setAmount('100.00');
        $this->request->setLocale('TR');
        $this->request->setPaymentGroup('PRODUCT');

        $data = $this->request->getData();

        $this->assertArrayHasKey('basketId', $data);
        $this->assertStringStartsWith('basket_', $data['basketId']);
        $this->assertNotEmpty($data['basketId']);
    }

    public function testGetDataBasketIdCanBeSet(): void
    {
        $this->request->setAmount('100.00');
        $this->request->setLocale('TR');
        $this->request->setPaymentGroup('PRODUCT');
        $this->request->setBasketId('custom_basket');

        $data = $this->request->getData();

        $this->assertSame('custom_basket', $data['basketId']);
    }

    public function testGetDataAutoGeneratesConversationId(): void
    {
        $this->request->setAmount('100.00');
        $this->request->setLocale('TR');
        $this->request->setPaymentGroup('PRODUCT');

        $data = $this->request->getData();

        $this->assertArrayHasKey('conversationId', $data);
        $this->assertStringStartsWith('txn_', $data['conversationId']);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setBasketId('order_123');
        $this->request->setPaymentGroup('PRODUCT');
        $this->request->setReturnUrl('https://example.com/callback');
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
                'token' => 'basic_bkm_token_123',
                'htmlContent' => '<form id="bkm-form">...</form>',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
        $this->assertSame('basic_bkm_token_123', $response->getToken());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_fail');
        $this->request->setBasketId('order_fail');
        $this->request->setPaymentGroup('PRODUCT');
        $this->request->setReturnUrl('https://example.com/callback');
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
                'errorMessage' => 'Basic BKM initialization failed',
                'errorGroup' => 'INIT_FAILURE',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5002', $response->getCode());
        $this->assertStringContainsString('Basic BKM initialization failed', $response->getMessage());
    }

    public function testSetAndGetBasketId(): void
    {
        $result = $this->request->setBasketId('order_456');
        $this->assertSame($this->request, $result);
        $this->assertSame('order_456', $this->request->getBasketId());
    }
}