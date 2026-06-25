<?php

namespace Omnipay\Iyzico\Tests\Message\Marketplace;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Marketplace\UpdateSubMerchantRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class UpdateSubMerchantRequestTest extends TestCase
{
    private UpdateSubMerchantRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new UpdateSubMerchantRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setSubMerchantKey('sub_merchant_key_001');
        $this->request->setPrice('1.0');
        $this->request->setName('John Doe Updated');
        $this->request->setEmail('john.updated@example.com');
        $this->request->setGsmNumber('+905551112234');
        $this->request->setAddress('Ankara, Turkey');
        $this->request->setIban('TR987654321098765432109876');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('sub_merchant_key_001', $data['subMerchantKey']);
        $this->assertSame('1.0', $data['price']);
        $this->assertSame('John Doe Updated', $data['name']);
        $this->assertSame('john.updated@example.com', $data['email']);
        $this->assertSame('+905551112234', $data['gsmNumber']);
        $this->assertSame('Ankara, Turkey', $data['address']);
        $this->assertSame('TR987654321098765432109876', $data['iban']);
        $this->assertSame('TRY', $data['currency']);
    }

    public function testGetDataThrowsWhenSubMerchantKeyMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The subMerchantKey parameter is required');

        $this->request->getData();
    }

    public function testGetSubMerchantKey(): void
    {
        $this->request->setSubMerchantKey('sub_merchant_key_002');

        $this->assertSame('sub_merchant_key_002', $this->request->getSubMerchantKey());
    }

    public function testSetSubMerchantKeyReturnsSelf(): void
    {
        $result = $this->request->setSubMerchantKey('sub_merchant_key_002');

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setSubMerchantKey('sub_merchant_key_001');
        $this->request->setPrice('1.0');
        $this->request->setName('John Doe Updated');
        $this->request->setEmail('john.updated@example.com');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('put')
            ->willReturn(json_encode([
                'status' => 'success',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_123',
                'subMerchantKey' => 'sub_merchant_key_001',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setSubMerchantKey('sub_merchant_key_fail');
        $this->request->setPrice('1.0');
        $this->request->setName('John Doe');
        $this->request->setEmail('john@example.com');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_fail');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('put')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '5002',
                'errorMessage' => 'Sub merchant not found for update',
                'errorGroup' => 'SUB_MERCHANT_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5002', $response->getCode());
        $this->assertStringContainsString('Sub merchant not found for update', $response->getMessage());
    }
}
