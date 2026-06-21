<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Iyzico\Message\FetchTransactionRequest;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class FetchTransactionRequestTest extends TestCase
{
    private FetchTransactionRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new FetchTransactionRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
        parent::tearDown();
    }

    public function testGetDataReturnsAllParameters(): void
    {
        $this->request->setPaymentId('pay_123');
        $this->request->setConversationId('conv_123');
        $this->request->setLocale('TR');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('pay_123', $data['paymentId']);
    }

    public function testGetDataThrowsWhenPaymentIdMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->setConversationId('conv_123');
        $this->request->getData();
    }

    public function testGetDataThrowsWhenConversationIdMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->setPaymentId('pay_123');
        $this->request->getData();
    }

    public function testGetDataThrowsWhenBothMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testSendDataCallsPaymentRetrieveAndReturnsResponse(): void
    {
        $this->request->setPaymentId('pay_123');
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
                'paymentId' => 'pay_123',
                'paymentStatus' => 'SUCCESS',
                'price' => '100.00',
                'paidPrice' => '100.00',
                'currency' => 'TRY',
                'basketId' => 'basket_123',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('pay_123', $response->getPaymentId());
        $this->assertSame('conv_123', $response->getConversationId());
        $this->assertSame('SUCCESS', $response->getPaymentStatus());
        $this->assertSame('pay_123', $response->getTransactionReference());
    }

    public function testSendDataWithFailedPaymentReturnsFailedResponse(): void
    {
        $this->request->setPaymentId('pay_fail');
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
                'errorMessage' => 'Payment not found',
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
        $this->assertStringContainsString('Payment not found', $response->getMessage());
    }

    public function testSendDataMapsLocaleCorrectly(): void
    {
        $this->request->setPaymentId('pay_123');
        $this->request->setConversationId('conv_123');
        $this->request->setLocale('EN');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'success',
                'locale' => 'EN',
                'conversationId' => 'conv_123',
                'paymentId' => 'pay_123',
                'paymentStatus' => 'SUCCESS',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}
