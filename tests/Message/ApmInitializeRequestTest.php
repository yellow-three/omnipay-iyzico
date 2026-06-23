<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\ApmInitializeRequest;
use Omnipay\Iyzico\Message\RedirectResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ApmInitializeRequestTest extends TestCase
{
    private ApmInitializeRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new ApmInitializeRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
        parent::tearDown();
    }

    public function testGetDataThrowsWhenAmountMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setAmount('50.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_apm');
        $this->request->setPaymentChannel('WEB');
        $this->request->setPaymentGroup('PRODUCT');
        $this->request->setApmType('SOFORT');
        $this->request->setMerchantOrderId('order_1');
        $this->request->setCountryCode('DE');
        $this->request->setReturnUrl('https://example.com/callback');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_apm', $data['conversationId']);
        $this->assertSame('50.00', $data['price']);
        $this->assertSame('50.00', $data['paidPrice']);
        $this->assertSame('TRY', $data['currency']);
        $this->assertSame('SOFORT', $data['apmType']);
        $this->assertSame('order_1', $data['merchantOrderId']);
        $this->assertSame('DE', $data['countryCode']);
        $this->assertSame('https://example.com/callback', $data['merchantCallbackUrl']);
    }

    public function testSetAndGetApmType(): void
    {
        $result = $this->request->setApmType('IDEAL');
        $this->assertSame($this->request, $result);
        $this->assertSame('IDEAL', $this->request->getApmType());
    }

    public function testSetAndGetMerchantOrderId(): void
    {
        $result = $this->request->setMerchantOrderId('ord_123');
        $this->assertSame($this->request, $result);
        $this->assertSame('ord_123', $this->request->getMerchantOrderId());
    }

    public function testSetAndGetCountryCode(): void
    {
        $result = $this->request->setCountryCode('NL');
        $this->assertSame($this->request, $result);
        $this->assertSame('NL', $this->request->getCountryCode());
    }

    public function testSetAndGetBasketId(): void
    {
        $result = $this->request->setBasketId('basket_99');
        $this->assertSame($this->request, $result);
        $this->assertSame('basket_99', $this->request->getBasketId());
    }

    public function testSendDataReturnsRedirectResponse(): void
    {
        $this->request->setAmount('50.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_apm');
        $this->request->setPaymentChannel('WEB');
        $this->request->setPaymentGroup('PRODUCT');
        $this->request->setApmType('SOFORT');
        $this->request->setMerchantOrderId('order_1');
        $this->request->setCountryCode('DE');
        $this->request->setReturnUrl('https://example.com/callback');
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
                'conversationId' => 'conv_apm',
                'redirectUrl' => 'https://sofort.com/pay/123',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}
