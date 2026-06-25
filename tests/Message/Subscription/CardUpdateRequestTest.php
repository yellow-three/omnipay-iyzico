<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Iyzico\Message\Subscription\CardUpdateRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class CardUpdateRequestTest extends TestCase
{
    private CardUpdateRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new CardUpdateRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setCustomerReferenceCode('customer_ref_001');
        $this->request->setCallbackUrl('https://example.com/callback');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('customer_ref_001', $data['customerReferenceCode']);
        $this->assertSame('https://example.com/callback', $data['callbackUrl']);
    }

    public function testGetDataThrowsWhenCustomerReferenceCodeMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The customerReferenceCode parameter is required');

        $this->request->setCallbackUrl('https://example.com/callback');
        $this->request->getData();
    }

    public function testGetDataThrowsWhenCallbackUrlMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The callbackUrl parameter is required');

        $this->request->setCustomerReferenceCode('customer_ref_001');
        $this->request->getData();
    }

    public function testGetCustomerReferenceCode(): void
    {
        $this->request->setCustomerReferenceCode('customer_ref_002');

        $this->assertSame('customer_ref_002', $this->request->getCustomerReferenceCode());
    }

    public function testSetCustomerReferenceCodeReturnsSelf(): void
    {
        $result = $this->request->setCustomerReferenceCode('customer_ref_002');

        $this->assertSame($this->request, $result);
    }

    public function testGetCallbackUrl(): void
    {
        $this->request->setCallbackUrl('https://example.com/callback');

        $this->assertSame('https://example.com/callback', $this->request->getCallbackUrl());
    }

    public function testSetCallbackUrlReturnsSelf(): void
    {
        $result = $this->request->setCallbackUrl('https://example.com/callback');

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setCustomerReferenceCode('customer_ref_001');
        $this->request->setCallbackUrl('https://example.com/callback');
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
                'token' => 'card_update_token_001',
                'tokenExpireTime' => '1735689600',
                'checkoutFormContent' => '<form>...</form>',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
        $this->assertSame('card_update_token_001', $response->getToken());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setCustomerReferenceCode('customer_ref_fail');
        $this->request->setCallbackUrl('https://example.com/callback');
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
                'errorMessage' => 'Customer not found',
                'errorGroup' => 'CUSTOMER_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5001', $response->getCode());
        $this->assertStringContainsString('Customer not found', $response->getMessage());
    }
}
