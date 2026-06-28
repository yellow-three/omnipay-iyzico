<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\PurchaseRequest;
use Omnipay\Iyzico\Message\RedirectResponse;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class PurchaseRequestTest extends TestCase
{
    private PurchaseRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new PurchaseRequest($httpClient, $httpRequest);

        $this->request->setApiKey('test-api-key');
        $this->request->setSecretKey('test-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');
        $this->request->setIdentityNumber('11111111111');
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);

        parent::tearDown();
    }

    private function createValidCard(): CreditCard
    {
        return new CreditCard([
            'number' => '4111111111111111',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'Test',
            'lastName' => 'User',
            'email' => 'test@example.com',
            'phone' => '+905551112233',
            'billingAddress1' => 'Test Address',
            'billingCity' => 'Istanbul',
            'billingCountry' => 'Turkey',
            'billingPostcode' => '34000',
            'shippingAddress1' => 'Test Address',
            'shippingCity' => 'Istanbul',
            'shippingCountry' => 'Turkey',
            'shippingPostcode' => '34000',
        ]);
    }

    public function testGetDataReturnsCorrectArrayStructure(): void
    {
        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('EN');
        $this->request->setConversationId('conv_123');
        $this->request->setInstallment(1);
        $this->request->setPaymentChannel('WEB');
        $this->request->setPaymentGroup('PRODUCT');
        $this->request->setReturnUrl('https://example.com/callback');
        $this->request->setSecure3d(true);
        $this->request->setClientIp('127.0.0.1');

        $data = $this->request->getData();

        $this->assertIsArray($data);
        $this->assertSame('EN', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('100.00', $data['price']);
        $this->assertSame('100.00', $data['paidPrice']);
        $this->assertSame('TRY', $data['currency']);
        $this->assertSame(1, $data['installment']);
        $this->assertSame('WEB', $data['paymentChannel']);
        $this->assertSame('PRODUCT', $data['paymentGroup']);
        $this->assertSame('https://example.com/callback', $data['callbackUrl']);
        $this->assertTrue($data['secure3d']);
        $this->assertSame('127.0.0.1', $data['clientIp']);
        $this->assertInstanceOf(CreditCard::class, $data['card']);
    }

    public function testGetDataThrowsExceptionWhenCardMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->setAmount('100.00');
        $this->request->getData();
    }

    public function testGetDataThrowsExceptionWhenAmountMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->getData();
    }

    public function testSendDataNon3dsCallsPaymentCreateAndReturnsResponse(): void
    {
        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'success',
                'paymentId' => 'pay_123',
                'conversationId' => 'conv_123',
                'price' => '100.00',
                'paidPrice' => '100.00',
                'installment' => 1,
                'currency' => 'TRY',
                'paymentStatus' => 'SUCCESS',
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setPaymentChannel('WEB');
        $this->request->setPaymentGroup('PRODUCT');
        $this->request->setSecure3d(false);
        $this->request->setReturnUrl('https://example.com/callback');

        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('pay_123', $response->getPaymentId());
        $this->assertSame('success', $response->getStatus());
    }

    public function testSendData3dsCallsThreedsInitializeCreateAndReturnsRedirectResponse(): void
    {
        $htmlContent = '<html><body><form id="3ds-form" action="https://bank.com/3ds" method="POST"></form></body></html>';
        $encodedHtml = base64_encode($htmlContent);

        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'success',
                'threeDSHtmlContent' => $encodedHtml,
                'paymentId' => 'pay_3ds_123',
                'conversationId' => 'conv_123',
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setPaymentChannel('WEB');
        $this->request->setPaymentGroup('PRODUCT');
        $this->request->setSecure3d(true);
        $this->request->setReturnUrl('https://example.com/callback');

        $response = $this->request->send();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isRedirect());
        $this->assertSame($htmlContent, $response->getHtmlContent());
        $this->assertSame('pay_3ds_123', $response->getPaymentId());
    }

    public function testGetDataIncludesPosOrderIdWhenSet(): void
    {
        $card = $this->createValidCard();
        $this->request->initialize([
            'card' => $card,
            'amount' => '100.00',
            'currency' => 'TRY',
            'locale' => 'TR',
            'paymentChannel' => 'WEB',
            'paymentGroup' => 'PRODUCT',
            'posOrderId' => 'pos_order_123',
        ]);

        $data = $this->request->getData();

        $this->assertSame('pos_order_123', $data['posOrderId']);
    }

    public function testGetDataPosOrderIdDefaultsToEmptyString(): void
    {
        $card = $this->createValidCard();
        $this->request->initialize([
            'card' => $card,
            'amount' => '100.00',
            'currency' => 'TRY',
            'locale' => 'TR',
            'paymentChannel' => 'WEB',
            'paymentGroup' => 'PRODUCT',
        ]);

        $data = $this->request->getData();

        $this->assertSame('', $data['posOrderId']);
    }

    public function testGetDataIncludesPaymentSourceWhenSet(): void
    {
        $card = $this->createValidCard();
        $this->request->initialize([
            'card' => $card,
            'amount' => '100.00',
            'currency' => 'TRY',
            'locale' => 'TR',
            'paymentChannel' => 'WEB',
            'paymentGroup' => 'PRODUCT',
            'paymentSource' => 'MOBILE',
        ]);

        $data = $this->request->getData();

        $this->assertSame('MOBILE', $data['paymentSource']);
    }

    public function testGetDataPaymentSourceDefaultsToEmptyString(): void
    {
        $card = $this->createValidCard();
        $this->request->initialize([
            'card' => $card,
            'amount' => '100.00',
            'currency' => 'TRY',
            'locale' => 'TR',
            'paymentChannel' => 'WEB',
            'paymentGroup' => 'PRODUCT',
        ]);

        $data = $this->request->getData();

        $this->assertSame('', $data['paymentSource']);
    }
}
