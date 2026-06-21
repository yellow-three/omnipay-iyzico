<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Iyzico\Message\CompletePurchaseRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;

class CompletePurchaseRequestTest extends TestCase
{
    private CompletePurchaseRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(\Omnipay\Common\Http\ClientInterface::class);
        $httpRequest = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);

        $this->request = new CompletePurchaseRequest($httpClient, $httpRequest);
        $this->request->setApiKey('test-api-key');
        $this->request->setSecretKey('test-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testSetConversationDataReturnsSelf(): void
    {
        $result = $this->request->setConversationData('some-conversation-data');
        $this->assertSame($this->request, $result);
    }

    public function testGetConversationData(): void
    {
        $this->request->setConversationData('conv-data-123');
        $this->assertSame('conv-data-123', $this->request->getConversationData());
    }

    public function testGetPaymentId(): void
    {
        $this->request->setPaymentId('pay-123');
        $this->assertSame('pay-123', $this->request->getPaymentId());
    }

    public function testGetConversationId(): void
    {
        $this->request->setConversationId('conv-456');
        $this->assertSame('conv-456', $this->request->getConversationId());
    }

    public function testGetConversationIdAutoGeneratesWhenNotSet(): void
    {
        $conversationId = $this->request->getConversationId();
        $this->assertNotEmpty($conversationId);
        $this->assertStringStartsWith('txn_', $conversationId);
    }

    public function testGetDataReturnsAllFields(): void
    {
        $this->request->setPaymentId('pay-789');
        $this->request->setConversationData('conv-data-abc');
        $this->request->setConversationId('manual-conv');

        $data = $this->request->getData();

        $this->assertSame([
            'conversationId' => 'manual-conv',
            'paymentId' => 'pay-789',
            'conversationData' => 'conv-data-abc',
        ], $data);
    }

    public function testGetDataAutoGeneratesConversationId(): void
    {
        $this->request->setPaymentId('pay-999');
        $this->request->setConversationData('data-xyz');

        $data = $this->request->getData();

        $this->assertArrayHasKey('conversationId', $data);
        $this->assertStringStartsWith('txn_', $data['conversationId']);
        $this->assertSame('pay-999', $data['paymentId']);
        $this->assertSame('data-xyz', $data['conversationData']);
    }

    public function testGetDataThrowsExceptionWhenPaymentIdMissing(): void
    {
        $this->request->setConversationData('some-data');

        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataThrowsExceptionWhenConversationDataMissing(): void
    {
        $this->request->setPaymentId('pay-123');

        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataThrowsExceptionWhenBothMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testSendDataReturnsResponseWithSuccessStatus(): void
    {
        $this->request->setPaymentId('pay-success');
        $this->request->setConversationData('data-success');

        $this->mockIyzicoHttpClient(json_encode([
            'status' => 'success',
            'paymentId' => 'pay-success',
            'conversationId' => 'conv_123',
            'price' => '100.00',
            'paidPrice' => '100.00',
            'installment' => 1,
            'currency' => 'TRY',
            'paymentStatus' => 'SUCCESS',
            'locale' => 'TR',
            'systemTime' => 1712345678,
        ]));

        $data = $this->request->getData();
        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('pay-success', $response->getPaymentId());
        $this->assertSame('100.00', $response->getPaidPrice());
    }

    public function testSendDataReturnsResponseWithFailureStatus(): void
    {
        $this->request->setPaymentId('pay-fail');
        $this->request->setConversationData('data-fail');

        $this->mockIyzicoHttpClient(json_encode([
            'status' => 'failure',
            'errorCode' => '5002',
            'errorMessage' => 'Ödeme bulunamadı',
            'errorGroup' => 'NOT_FOUND',
            'locale' => 'TR',
            'systemTime' => 1712345678,
        ]));

        $data = $this->request->getData();
        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5002', $response->getCode());
        $this->assertStringContainsString('Ödeme bulunamadı', $response->getMessage() ?? '');
    }

    public function testSendDataPassesPaymentIdToSdk(): void
    {
        $this->request->setPaymentId('pay-final');
        $this->request->setConversationData('data-final');
        $this->request->setConversationId('conv-final');

        $this->mockIyzicoHttpClient(json_encode([
            'status' => 'success',
            'paymentId' => 'pay-final',
            'conversationId' => 'conv-final',
            'locale' => 'TR',
            'systemTime' => 1712345678,
        ]));

        $data = $this->request->getData();
        $response = $this->request->sendData($data);

        $this->assertSame('pay-final', $response->getPaymentId());
        $this->assertSame('conv-final', $response->getConversationId());
    }

    private function mockIyzicoHttpClient(string $responseJson): void
    {
        $mockSdkHttp = $this->createMock(HttpClient::class);
        $mockSdkHttp->method('post')->willReturn($responseJson);

        ApiResource::setHttpClient($mockSdkHttp);
    }
}
