<?php

namespace Omnipay\Iyzico\Tests\Message\Marketplace;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Marketplace\SubMerchantPaymentItemUpdateRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class SubMerchantPaymentItemUpdateRequestTest extends TestCase
{
    private SubMerchantPaymentItemUpdateRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new SubMerchantPaymentItemUpdateRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setPaymentTransactionId('tx_123');
        $this->request->setSubMerchantKey('sub_merchant_key_001');
        $this->request->setSubMerchantPrice('25.00');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('tx_123', $data['paymentTransactionId']);
        $this->assertSame('sub_merchant_key_001', $data['subMerchantKey']);
        $this->assertSame('25.00', $data['subMerchantPrice']);
    }

    public function testGetDataThrowsWhenPaymentTransactionIdMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The paymentTransactionId parameter is required');

        $this->request->getData();
    }

    public function testGetPaymentTransactionId(): void
    {
        $this->request->setPaymentTransactionId('tx_456');

        $this->assertSame('tx_456', $this->request->getPaymentTransactionId());
    }

    public function testSetPaymentTransactionIdReturnsSelf(): void
    {
        $result = $this->request->setPaymentTransactionId('tx_456');

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setPaymentTransactionId('tx_123');
        $this->request->setSubMerchantKey('sub_merchant_key_001');
        $this->request->setSubMerchantPrice('25.00');
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
                'paymentTransactionId' => 'tx_123',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setPaymentTransactionId('tx_fail');
        $this->request->setSubMerchantKey('sub_merchant_key_fail');
        $this->request->setSubMerchantPrice('25.00');
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
                'errorCode' => '5008',
                'errorMessage' => 'Sub merchant payment item update failed',
                'errorGroup' => 'PAYMENT_ITEM_UPDATE_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5008', $response->getCode());
        $this->assertStringContainsString('Sub merchant payment item update failed', $response->getMessage());
    }
}
