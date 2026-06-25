<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Iyzico\Message\Subscription\UpdateProductRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class UpdateProductRequestTest extends TestCase
{
    private UpdateProductRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new UpdateProductRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setProductReferenceCode('product_ref_001');
        $this->request->setName('Updated Plan');
        $this->request->setDescription('Updated subscription plan');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('product_ref_001', $data['productReferenceCode']);
        $this->assertSame('Updated Plan', $data['name']);
        $this->assertSame('Updated subscription plan', $data['description']);
    }

    public function testGetDataThrowsWhenProductReferenceCodeMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The productReferenceCode parameter is required');

        $this->request->getData();
    }

    public function testGetProductReferenceCode(): void
    {
        $this->request->setProductReferenceCode('product_ref_002');

        $this->assertSame('product_ref_002', $this->request->getProductReferenceCode());
    }

    public function testSetProductReferenceCodeReturnsSelf(): void
    {
        $result = $this->request->setProductReferenceCode('product_ref_002');

        $this->assertSame($this->request, $result);
    }

    public function testGetName(): void
    {
        $this->request->setName('Basic Plan');

        $this->assertSame('Basic Plan', $this->request->getName());
    }

    public function testSetNameReturnsSelf(): void
    {
        $result = $this->request->setName('Basic Plan');

        $this->assertSame($this->request, $result);
    }

    public function testGetDescription(): void
    {
        $this->request->setDescription('Basic subscription plan');

        $this->assertSame('Basic subscription plan', $this->request->getDescription());
    }

    public function testSetDescriptionReturnsSelf(): void
    {
        $result = $this->request->setDescription('Basic subscription plan');

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setProductReferenceCode('product_ref_001');
        $this->request->setName('Updated Plan');
        $this->request->setDescription('Updated subscription plan');
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
                'name' => 'Updated Plan',
                'description' => 'Updated subscription plan',
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
        $this->request->setProductReferenceCode('product_ref_not_found');
        $this->request->setName('Updated Plan');
        $this->request->setDescription('Updated subscription plan');
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
                'errorCode' => '5002',
                'errorMessage' => 'Product not found',
                'errorGroup' => 'PRODUCT_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5002', $response->getCode());
        $this->assertStringContainsString('Product not found', $response->getMessage());
    }
}
