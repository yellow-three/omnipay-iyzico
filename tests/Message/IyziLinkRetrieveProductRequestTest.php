<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\IyziLinkRetrieveProductRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class IyziLinkRetrieveProductRequestTest extends TestCase
{
    private IyziLinkRetrieveProductRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new IyziLinkRetrieveProductRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
        parent::tearDown();
    }

    public function testGetDataThrowsWhenTokenMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_456');
        $this->request->setToken('token_xyz');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_456', $data['conversationId']);
        $this->assertSame('token_xyz', $data['token']);
    }

    public function testSetAndGetToken(): void
    {
        $result = $this->request->setToken('tok_abc');
        $this->assertSame($this->request, $result);
        $this->assertSame('tok_abc', $this->request->getToken());
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_789');
        $this->request->setToken('token_ret');
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
                'conversationId' => 'conv_789',
                'token' => 'token_ret',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}
