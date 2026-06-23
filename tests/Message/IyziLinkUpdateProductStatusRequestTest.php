<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\DefaultHttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\IyziLinkUpdateProductStatusRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class IyziLinkUpdateProductStatusRequestTest extends TestCase
{
    private IyziLinkUpdateProductStatusRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new IyziLinkUpdateProductStatusRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
        parent::tearDown();
    }

    public function testGetDataThrowsWhenTokenMissing(): void
    {
        $this->request->setProductStatus('ACTIVE');
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataThrowsWhenProductStatusMissing(): void
    {
        $this->request->setToken('token_abc');
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_upd');
        $this->request->setToken('token_upd');
        $this->request->setProductStatus('ACTIVE');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_upd', $data['conversationId']);
        $this->assertSame('token_upd', $data['token']);
        $this->assertSame('ACTIVE', $data['productStatus']);
    }

    public function testSetAndGetToken(): void
    {
        $result = $this->request->setToken('tok_upd');
        $this->assertSame($this->request, $result);
        $this->assertSame('tok_upd', $this->request->getToken());
    }

    public function testSetAndGetProductStatus(): void
    {
        $result = $this->request->setProductStatus('PASSIVE');
        $this->assertSame($this->request, $result);
        $this->assertSame('PASSIVE', $this->request->getProductStatus());
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_upd');
        $this->request->setToken('token_upd');
        $this->request->setProductStatus('ACTIVE');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(DefaultHttpClient::class);
        $httpClient->expects($this->once())
            ->method('patch')
            ->willReturn(json_encode([
                'status' => 'success',
                'locale' => 'TR',
                'conversationId' => 'conv_upd',
                'token' => 'token_upd',
                'productStatus' => 'ACTIVE',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}
