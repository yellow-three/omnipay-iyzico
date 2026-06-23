<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Iyzico\Message\BasicThreedsPreAuthRequest;
use Omnipay\Iyzico\Message\RedirectResponse;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class BasicThreedsPreAuthRequestTest extends TestCase
{
    private BasicThreedsPreAuthRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new BasicThreedsPreAuthRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
        parent::tearDown();
    }

    public function testGetDataReturnsAllParameters(): void
    {
        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setInstallment(1);
        $this->request->setReturnUrl('https://example.com/callback');

        $this->request->setCard([
            'number' => '4111111111111111',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+905551112233',
            'billingAddress1' => 'Test Street',
            'billingCity' => 'Istanbul',
            'billingCountry' => 'Turkey',
            'billingPostcode' => '34700',
        ]);

        $this->request->setClientIp('192.168.1.1');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('100.00', $data['price']);
        $this->assertSame('100.00', $data['paidPrice']);
        $this->assertSame('TRY', $data['currency']);
        $this->assertSame(1, $data['installment']);
        $this->assertSame('https://example.com/callback', $data['callbackUrl']);
        $this->assertSame('192.168.1.1', $data['clientIp']);
        $this->assertNotNull($data['card']);
    }

    public function testGetDataThrowsWhenCardMissing(): void
    {
        $this->request->setAmount('100.00');

        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataThrowsWhenAmountMissing(): void
    {
        $this->request->setCard([
            'number' => '4111111111111111',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataAutoGeneratesConversationId(): void
    {
        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');

        $this->request->setCard([
            'number' => '4111111111111111',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $data = $this->request->getData();

        $this->assertArrayHasKey('conversationId', $data);
        $this->assertStringStartsWith('txn_', $data['conversationId']);
    }

    public function testSendDataReturnsRedirectResponseWithHtmlContent(): void
    {
        $htmlContent = '<form id="iyzico-3ds-form" action="https://bank.3ds-page.com" method="POST">...</form>';

        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setInstallment(1);
        $this->request->setReturnUrl('https://example.com/callback');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');
        $this->request->setClientIp('192.168.1.1');

        $this->request->setCard([
            'number' => '4111111111111111',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'billingAddress1' => 'Test Street',
            'billingCity' => 'Istanbul',
            'billingCountry' => 'Turkey',
            'billingPostcode' => '34700',
        ]);

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'success',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_123',
                'threeDSHtmlContent' => base64_encode($htmlContent),
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertSame($htmlContent, $response->getHtmlContent());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_fail');
        $this->request->setInstallment(1);
        $this->request->setReturnUrl('https://example.com/callback');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');
        $this->request->setClientIp('192.168.1.1');

        $this->request->setCard([
            'number' => '4111111111111111',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'billingAddress1' => 'Test Street',
            'billingCity' => 'Istanbul',
            'billingCountry' => 'Turkey',
            'billingPostcode' => '34700',
        ]);

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '5001',
                'errorMessage' => 'Basic 3ds preauth initialization failed',
                'errorGroup' => 'INIT_FAILURE',
                'locale' => 'TR',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('5001', $response->getCode());
        $this->assertStringContainsString('Basic 3ds preauth initialization failed', $response->getMessage());
    }

    public function testSendDataMapsCurrencyCorrectly(): void
    {
        $htmlContent = '<form id="iyzico-3ds-form">...</form>';

        $this->request->setAmount('200.00');
        $this->request->setCurrency('USD');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_curr');
        $this->request->setInstallment(1);
        $this->request->setReturnUrl('https://example.com/callback');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');
        $this->request->setClientIp('192.168.1.1');

        $this->request->setCard([
            'number' => '4111111111111111',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'billingAddress1' => 'Test Street',
            'billingCity' => 'Istanbul',
            'billingCountry' => 'Turkey',
            'billingPostcode' => '34700',
        ]);

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('/payment/3dsecure/initialize/preauth/basic'),
                $this->anything(),
                $this->anything()
            )
            ->willReturn(json_encode([
                'status' => 'success',
                'locale' => 'TR',
                'conversationId' => 'conv_curr',
                'threeDSHtmlContent' => base64_encode($htmlContent),
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}
