<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\IyziLinkSearchMerchantProductsRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class IyziLinkSearchMerchantProductsRequestTest extends TestCase
{
    private IyziLinkSearchMerchantProductsRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new IyziLinkSearchMerchantProductsRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
        parent::tearDown();
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_search');
        $this->request->setPage(3);
        $this->request->setCount(15);

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_search', $data['conversationId']);
        $this->assertSame(3, $data['page']);
        $this->assertSame(15, $data['count']);
    }

    public function testGetDataDefaultsPageAndCount(): void
    {
        $this->request->setLocale('TR');

        $data = $this->request->getData();

        $this->assertSame(0, $data['page']);
        $this->assertSame(0, $data['count']);
    }

    public function testSetAndGetPage(): void
    {
        $result = $this->request->setPage(7);
        $this->assertSame($this->request, $result);
        $this->assertSame(7, $this->request->getPage());
    }

    public function testSetAndGetCount(): void
    {
        $result = $this->request->setCount(25);
        $this->assertSame($this->request, $result);
        $this->assertSame(25, $this->request->getCount());
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_search');
        $this->request->setPage(1);
        $this->request->setCount(5);
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('getV2')
            ->willReturn(json_encode([
                'status' => 'success',
                'locale' => 'TR',
                'conversationId' => 'conv_search',
                'items' => [],
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}
