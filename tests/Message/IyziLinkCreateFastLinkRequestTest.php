<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\IyziLinkCreateFastLinkRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class IyziLinkCreateFastLinkRequestTest extends TestCase
{
    private IyziLinkCreateFastLinkRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new IyziLinkCreateFastLinkRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
        parent::tearDown();
    }

    public function testGetDataThrowsWhenDescriptionMissing(): void
    {
        $this->request->setPrice(50.00);
        $this->request->setCurrencyCode('TRY');
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataThrowsWhenPriceMissing(): void
    {
        $this->request->setDescription('Test');
        $this->request->setCurrencyCode('TRY');
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataThrowsWhenCurrencyCodeMissing(): void
    {
        $this->request->setDescription('Test');
        $this->request->setPrice(50.00);
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_fast');
        $this->request->setDescription('Quick payment');
        $this->request->setPrice(25.50);
        $this->request->setCurrencyCode('EUR');
        $this->request->setSourceType('MOBILE');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_fast', $data['conversationId']);
        $this->assertSame('Quick payment', $data['description']);
        $this->assertSame(25.50, $data['price']);
        $this->assertSame('EUR', $data['currencyCode']);
        $this->assertSame('MOBILE', $data['sourceType']);
    }

    public function testSourceTypeDefaultsToWeb(): void
    {
        $this->assertSame('WEB', $this->request->getSourceType());
    }

    public function testSetAndGetDescription(): void
    {
        $result = $this->request->setDescription('desc');
        $this->assertSame($this->request, $result);
        $this->assertSame('desc', $this->request->getDescription());
    }

    public function testSetAndGetPrice(): void
    {
        $result = $this->request->setPrice(99.99);
        $this->assertSame($this->request, $result);
        $this->assertSame(99.99, $this->request->getPrice());
    }

    public function testSetAndGetCurrencyCode(): void
    {
        $result = $this->request->setCurrencyCode('USD');
        $this->assertSame($this->request, $result);
        $this->assertSame('USD', $this->request->getCurrencyCode());
    }

    public function testSetAndGetSourceType(): void
    {
        $result = $this->request->setSourceType('MOBILE');
        $this->assertSame($this->request, $result);
        $this->assertSame('MOBILE', $this->request->getSourceType());
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_fast');
        $this->request->setDescription('Fast link');
        $this->request->setPrice(10.00);
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
                'conversationId' => 'conv_fast',
                'shortUrl' => 'https://iyzico.link/fast123',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}
