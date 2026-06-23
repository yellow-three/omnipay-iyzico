<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\IyziLinkSaveProductRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class IyziLinkSaveProductRequestTest extends TestCase
{
    private IyziLinkSaveProductRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new IyziLinkSaveProductRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
        parent::tearDown();
    }

    public function testGetDataThrowsWhenNameMissing(): void
    {
        $this->request->setPrice(100.00);
        $this->request->setCurrencyCode('TRY');
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The name parameter is required');

        $this->request->getData();
    }

    public function testGetDataThrowsWhenPriceMissing(): void
    {
        $this->request->setName('Test Product');
        $this->request->setCurrencyCode('TRY');
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The price parameter is required');

        $this->request->getData();
    }

    public function testGetDataThrowsWhenCurrencyCodeMissing(): void
    {
        $this->request->setName('Test Product');
        $this->request->setPrice(100.00);
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The currencyCode parameter is required');

        $this->request->getData();
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setLocale('TR');
        $this->request->setName('Test Product');
        $this->request->setProductDescription('Test description');
        $this->request->setPrice(99.99);
        $this->request->setCurrencyCode('TRY');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('Test Product', $data['name']);
        $this->assertSame('Test description', $data['description']);
        $this->assertSame(99.99, $data['price']);
        $this->assertSame('TRY', $data['currencyCode']);
    }

    public function testSetAndGetName(): void
    {
        $result = $this->request->setName('Product Name');
        $this->assertSame($this->request, $result);
        $this->assertSame('Product Name', $this->request->getName());
    }

    public function testSetAndGetPrice(): void
    {
        $result = $this->request->setPrice(50.00);
        $this->assertSame($this->request, $result);
        $this->assertSame(50.00, $this->request->getPrice());
    }

    public function testSetAndGetCurrencyCode(): void
    {
        $result = $this->request->setCurrencyCode('USD');
        $this->assertSame($this->request, $result);
        $this->assertSame('USD', $this->request->getCurrencyCode());
    }

    public function testSourceTypeDefaultsToWeb(): void
    {
        $this->assertSame('WEB', $this->request->getSourceType());
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setLocale('TR');
        $this->request->setName('Test Product');
        $this->request->setPrice(99.99);
        $this->request->setCurrencyCode('TRY');
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
                'conversationId' => 'conv_123',
                'token' => 'token_123',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}