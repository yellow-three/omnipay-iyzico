<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Iyzico\Message\Subscription\ListCustomersRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ListCustomersRequestTest extends TestCase
{
    private ListCustomersRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new ListCustomersRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setPage(1);
        $this->request->setCount(10);

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame(1, $data['page']);
        $this->assertSame(10, $data['count']);
    }

    public function testGetDataReturnsDefaultsWhenNotSet(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_default');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_default', $data['conversationId']);
        $this->assertNull($data['page']);
        $this->assertNull($data['count']);
    }

    public function testSetAndGetPage(): void
    {
        $result = $this->request->setPage(2);
        $this->assertSame($this->request, $result);
        $this->assertSame(2, $this->request->getPage());
    }

    public function testSetAndGetCount(): void
    {
        $result = $this->request->setCount(20);
        $this->assertSame($this->request, $result);
        $this->assertSame(20, $this->request->getCount());
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setPage(1);
        $this->request->setCount(10);
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
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_123',
                'totalCount' => 5,
                'currentPage' => 1,
                'pageCount' => 1,
                'items' => [
                    [
                        'referenceCode' => 'cust_ref_001',
                        'email' => 'john@example.com',
                        'name' => 'John',
                    ],
                ],
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_fail');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('getV2')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '5000',
                'errorMessage' => 'An error occurred',
                'errorGroup' => 'LIST_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5000', $response->getCode());
        $this->assertStringContainsString('An error occurred', $response->getMessage());
    }
}
