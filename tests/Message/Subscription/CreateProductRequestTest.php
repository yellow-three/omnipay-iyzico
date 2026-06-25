<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Iyzico\Message\Subscription\CreateProductRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class CreateProductRequestTest extends TestCase
{
    private CreateProductRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new CreateProductRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setName('Monthly Subscription');
        $this->request->setDescription('Monthly subscription product');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('Monthly Subscription', $data['name']);
        $this->assertSame('Monthly subscription product', $data['description']);
    }

    public function testGetDataThrowsWhenNameMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The name parameter is required');

        $this->request->getData();
    }

    public function testGetName(): void
    {
        $this->request->setName('Premium Plan');

        $this->assertSame('Premium Plan', $this->request->getName());
    }

    public function testSetNameReturnsSelf(): void
    {
        $result = $this->request->setName('Premium Plan');

        $this->assertSame($this->request, $result);
    }

    public function testGetDescription(): void
    {
        $this->request->setDescription('Premium subscription plan');

        $this->assertSame('Premium subscription plan', $this->request->getDescription());
    }

    public function testSetDescriptionReturnsSelf(): void
    {
        $result = $this->request->setDescription('Premium subscription plan');

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setName('Monthly Subscription');
        $this->request->setDescription('Monthly subscription product');
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
                'name' => 'Monthly Subscription',
                'description' => 'Monthly subscription product',
                'referenceCode' => 'product_ref_001',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setName('Monthly Subscription');
        $this->request->setDescription('Monthly subscription product');
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
                'errorCode' => '5001',
                'errorMessage' => 'Product already exists',
                'errorGroup' => 'PRODUCT_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5001', $response->getCode());
        $this->assertStringContainsString('Product already exists', $response->getMessage());
    }
}
