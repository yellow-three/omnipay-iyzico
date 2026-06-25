<?php

namespace Omnipay\Iyzico\Tests\Message\Marketplace;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Marketplace\CreateSubMerchantRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class CreateSubMerchantRequestTest extends TestCase
{
    private CreateSubMerchantRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new CreateSubMerchantRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setSubMerchantExternalId('ext_merchant_001');
        $this->request->setSubMerchantType('PERSONAL');
        $this->request->setPrice('1.0');
        $this->request->setName('John Doe');
        $this->request->setEmail('john@example.com');
        $this->request->setGsmNumber('+905551112233');
        $this->request->setAddress('Istanbul, Turkey');
        $this->request->setIban('TR123456789012345678901234');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('ext_merchant_001', $data['subMerchantExternalId']);
        $this->assertSame('PERSONAL', $data['subMerchantType']);
        $this->assertSame('1.0', $data['price']);
        $this->assertSame('John Doe', $data['name']);
        $this->assertSame('john@example.com', $data['email']);
        $this->assertSame('+905551112233', $data['gsmNumber']);
        $this->assertSame('Istanbul, Turkey', $data['address']);
        $this->assertSame('TR123456789012345678901234', $data['iban']);
        $this->assertSame('TRY', $data['currency']);
    }

    public function testGetDataThrowsWhenSubMerchantExternalIdMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The subMerchantExternalId parameter is required');

        $this->request->getData();
    }

    public function testGetSubMerchantExternalId(): void
    {
        $this->request->setSubMerchantExternalId('ext_merchant_002');

        $this->assertSame('ext_merchant_002', $this->request->getSubMerchantExternalId());
    }

    public function testSetSubMerchantExternalIdReturnsSelf(): void
    {
        $result = $this->request->setSubMerchantExternalId('ext_merchant_002');

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setSubMerchantExternalId('ext_merchant_001');
        $this->request->setSubMerchantType('PERSONAL');
        $this->request->setPrice('1.0');
        $this->request->setName('John Doe');
        $this->request->setEmail('john@example.com');
        $this->request->setGsmNumber('+905551112233');
        $this->request->setAddress('Istanbul, Turkey');
        $this->request->setIban('TR123456789012345678901234');
        $this->request->setCurrency('TRY');
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
        $this->request->setSubMerchantExternalId('ext_merchant_fail');
        $this->request->setSubMerchantType('PERSONAL');
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
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '5001',
                'errorMessage' => 'Sub merchant already exists',
                'errorGroup' => 'SUB_MERCHANT_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5001', $response->getCode());
        $this->assertStringContainsString('Sub merchant already exists', $response->getMessage());
    }
}
