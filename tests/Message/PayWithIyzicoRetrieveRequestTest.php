<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Iyzico\Message\PayWithIyzicoRetrieveRequest;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class PayWithIyzicoRetrieveRequestTest extends TestCase
{
    private PayWithIyzicoRetrieveRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new PayWithIyzicoRetrieveRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
        parent::tearDown();
    }

    public function testGetDataReturnsToken(): void
    {
        $this->request->setToken('pwi_token_123');

        $data = $this->request->getData();

        $this->assertSame('pwi_token_123', $data['token']);
    }

    public function testGetDataThrowsWhenTokenMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setToken('pwi_token_123');
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
                'token' => 'pwi_token_123',
                'paymentStatus' => 'SUCCESS',
                'signature' => 'signature_123',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('pwi_token_123', $response->getToken());
    }

    public function testSendDataWithFailureResponse(): void
    {
        $this->request->setToken('pwi_token_fail');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '404',
                'errorMessage' => 'Payment not found',
                'locale' => 'TR',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('404', $response->getCode());
        $this->assertStringContainsString('Payment not found', $response->getMessage());
    }

    public function testSetAndGetToken(): void
    {
        $result = $this->request->setToken('pwi_token_456');
        $this->assertSame($this->request, $result);
        $this->assertSame('pwi_token_456', $this->request->getToken());
    }
}
