<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Iyzico\Message\CheckoutStatusRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;

class CheckoutStatusRequestTest extends TestCase
{
    private CheckoutStatusRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(\Omnipay\Common\Http\ClientInterface::class);
        $httpRequest = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);

        $this->request = new CheckoutStatusRequest($httpClient, $httpRequest);
        $this->request->setApiKey('test-api-key');
        $this->request->setSecretKey('test-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testSetTokenReturnsSelf(): void
    {
        $result = $this->request->setToken('test-token');
        $this->assertSame($this->request, $result);
    }

    public function testGetToken(): void
    {
        $this->request->setToken('my-token-value');
        $this->assertSame('my-token-value', $this->request->getToken());
    }

    public function testGetDataReturnsToken(): void
    {
        $this->request->setToken('token-123');

        $data = $this->request->getData();

        $this->assertSame(['token' => 'token-123'], $data);
    }

    public function testGetDataThrowsExceptionWhenTokenMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testSendDataReturnsResponseWithSuccessStatus(): void
    {
        $this->request->setToken('test-token');

        $this->mockIyzicoHttpClient(json_encode([
            'status' => 'success',
            'token' => 'test-token',
            'paymentId' => 'payment-123',
            'conversationId' => 'conv_123',
            'locale' => 'TR',
            'systemTime' => 1712345678,
        ]));

        $data = $this->request->getData();
        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('test-token', $response->getToken());
        $this->assertSame('payment-123', $response->getPaymentId());
    }

    public function testSendDataReturnsResponseWithFailureStatus(): void
    {
        $this->request->setToken('invalid-token');

        $this->mockIyzicoHttpClient(json_encode([
            'status' => 'failure',
            'errorCode' => '5001',
            'errorMessage' => 'Token bulunamadı',
            'errorGroup' => 'NOT_FOUND',
            'locale' => 'TR',
            'systemTime' => 1712345678,
        ]));

        $data = $this->request->getData();
        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5001', $response->getCode());
        $this->assertStringContainsString('Token bulunamadı', $response->getMessage() ?? '');
    }

    public function testSendDataUsesTokenFromGetData(): void
    {
        $this->request->setToken('token-from-setter');

        $this->mockIyzicoHttpClient(json_encode([
            'status' => 'success',
            'token' => 'token-from-setter',
            'paymentId' => 'payment-123',
            'conversationId' => 'conv_123',
            'locale' => 'TR',
            'systemTime' => 1712345678,
        ]));

        $data = $this->request->getData();
        $this->assertSame('token-from-setter', $data['token']);

        $response = $this->request->sendData($data);

        $this->assertTrue($response->isSuccessful());
    }

    private function mockIyzicoHttpClient(string $responseJson): void
    {
        $mockSdkHttp = $this->createMock(HttpClient::class);
        $mockSdkHttp->method('post')->willReturn($responseJson);

        ApiResource::setHttpClient($mockSdkHttp);
    }
}
