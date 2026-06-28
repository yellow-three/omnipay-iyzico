<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\AuthorizeRequest;
use Omnipay\Iyzico\Message\RedirectResponse;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class AuthorizeRequestTest extends TestCase
{
    private AuthorizeRequest $request;

    private ClientInterface $httpClient;

    private HttpRequest $httpRequest;

    private \Iyzipay\HttpClient $iyzicoHttpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->httpRequest = new HttpRequest();

        $this->request = new AuthorizeRequest($this->httpClient, $this->httpRequest);

        // Inject a mock HTTP client into the iyzico SDK so PaymentPreAuth::create()
        // and ThreedsInitialize::create() don't make real API calls.
        $this->iyzicoHttpClient = $this->createMock(\Iyzipay\HttpClient::class);
        ApiResource::setHttpClient($this->iyzicoHttpClient);
    }

    protected function tearDown(): void
    {
        // Reset the iyzico global HTTP client so other tests are not affected.
        ApiResource::setHttpClient(null);
    }

    // ---------------------------------------------------------------
    //  Helpers
    // ---------------------------------------------------------------

    private function getValidCardData(): array
    {
        return [
            'number' => '4111111111111111',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'billingAddress1' => 'Test St. 123',
            'billingCity' => 'Istanbul',
            'billingCountry' => 'Turkey',
            'billingPostcode' => '34000',
            'shippingAddress1' => 'Test St. 123',
            'shippingCity' => 'Istanbul',
            'shippingCountry' => 'Turkey',
            'shippingPostcode' => '34000',
            'email' => 'john@example.com',
            'phone' => '+905551112233',
        ];
    }

    private function initializeRequest(array $overrides = []): void
    {
        $defaults = [
            'amount' => '100.00',
            'currency' => 'TRY',
            'card' => $this->getValidCardData(),
            'secure3d' => false,
            'locale' => 'TR',
            'conversationId' => 'conv_123',
            'installment' => 1,
            'paymentChannel' => 'WEB',
            'paymentGroup' => 'PRODUCT',
            'description' => 'Test payment',
            'clientIp' => '127.0.0.1',
            'returnUrl' => 'https://example.com/callback',
            'identityNumber' => '11111111111',
            'apiKey' => 'test-api-key',
            'secretKey' => 'test-secret-key',
            'baseUrl' => 'https://sandbox-api.iyzipay.com',
        ];

        $this->request->initialize(array_merge($defaults, $overrides));
    }

    /**
     * Build a JSON string that mimics a successful iyzico PaymentPreAuth response.
     */
    private function buildPaymentPreAuthJson(): string
    {
        return json_encode([
            'status' => 'success',
            'paymentId' => 'pay_12345',
            'conversationId' => 'conv_123',
            'price' => '100.00',
            'paidPrice' => '100.00',
            'installment' => 1,
            'currency' => 'TRY',
            'locale' => 'tr',
            'systemTime' => 1719000000,
        ]);
    }

    /**
     * Build a JSON string that mimics a successful iyzico ThreedsInitialize response.
     */
    private function buildThreedsInitializeJson(): string
    {
        $htmlContent = '<form id="iyzipay-checkout-form" action="https://bank.com/3ds" method="POST">'
            . '<input type="hidden" name="token" value="abc123" />'
            . '</form>';

        return json_encode([
            'status' => 'success',
            'paymentId' => 'pay_67890',
            'conversationId' => 'conv_456',
            'threeDSHtmlContent' => base64_encode($htmlContent),
            'locale' => 'tr',
            'systemTime' => 1719000000,
        ]);
    }

    // ---------------------------------------------------------------
    //  getData() – validation & structure
    // ---------------------------------------------------------------

    public function testGetDataReturnsExpectedStructure(): void
    {
        $this->initializeRequest();

        $data = $this->request->getData();

        $expectedKeys = [
            'locale',
            'conversationId',
            'price',
            'paidPrice',
            'currency',
            'installment',
            'paymentChannel',
            'paymentGroup',
            'callbackUrl',
            'secure3d',
            'card',
            'clientIp',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data, "Missing key: $key");
        }

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('100.00', $data['price']);
        $this->assertSame('100.00', $data['paidPrice']);
        $this->assertSame('TRY', $data['currency']);
        $this->assertSame(1, $data['installment']);
        $this->assertSame('WEB', $data['paymentChannel']);
        $this->assertSame('PRODUCT', $data['paymentGroup']);
        $this->assertSame('https://example.com/callback', $data['callbackUrl']);
        $this->assertFalse($data['secure3d']);
        $this->assertSame('127.0.0.1', $data['clientIp']);
        $this->assertInstanceOf(\Omnipay\Common\CreditCard::class, $data['card']);
    }

    public function testGetDataThrowsOnMissingCard(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('card');

        $this->initializeRequest(['card' => null]);
        $this->request->getData();
    }

    public function testGetDataThrowsOnMissingAmount(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('amount');

        $this->initializeRequest(['amount' => null]);
        $this->request->getData();
    }

    public function testGetDataThrowsOnInvalidCard(): void
    {
        $this->expectException(InvalidCreditCardException::class);

        $this->initializeRequest([
            'card' => [
                'number' => '4111111111111111',
                // Missing expiryMonth, expiryYear, firstName, lastName, etc.
            ],
        ]);

        $this->request->getData();
    }

    public function testGetDataPassesSecure3dFlag(): void
    {
        $this->initializeRequest(['secure3d' => true]);

        $data = $this->request->getData();

        $this->assertTrue($data['secure3d']);
    }

    public function testGetDataGeneratesConversationIdWhenNotProvided(): void
    {
        $this->initializeRequest(['conversationId' => '']);

        $data = $this->request->getData();

        $this->assertNotEmpty($data['conversationId']);
        $this->assertStringStartsWith('txn_', $data['conversationId']);
    }

    public function testGetDataReturnsCallbackUrlOnlyWhenSet(): void
    {
        $this->initializeRequest(['returnUrl' => 'https://example.com/return']);

        $data = $this->request->getData();

        $this->assertSame('https://example.com/return', $data['callbackUrl']);
    }

    public function testGetDataReturnsEmptyCallbackUrlWhenNotSet(): void
    {
        $this->initializeRequest(['returnUrl' => null]);

        $data = $this->request->getData();

        $this->assertEmpty($data['callbackUrl']);
    }

    // ---------------------------------------------------------------
    //  sendData() – non-3DS (PaymentPreAuth)
    // ---------------------------------------------------------------

    public function testSendDataNonSecure3dCallsPaymentPreAuth(): void
    {
        $this->initializeRequest([
            'secure3d' => false,
            'apiKey' => 'test-api-key',
            'secretKey' => 'test-secret-key',
            'baseUrl' => 'https://sandbox-api.iyzipay.com',
        ]);

        $jsonResponse = $this->buildPaymentPreAuthJson();

        $this->iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('/payment/preauth'),
                $this->anything(),
                $this->anything()
            )
            ->willReturn($jsonResponse);

        /** @var Response $response */
        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('pay_12345', $response->getPaymentId());
        $this->assertSame('pay_12345', $response->getTransactionReference());
    }

    public function testSendDataNonSecure3dStoresErrorResult(): void
    {
        $this->initializeRequest([
            'secure3d' => false,
            'apiKey' => 'test-api-key',
            'secretKey' => 'test-secret-key',
            'baseUrl' => 'https://sandbox-api.iyzipay.com',
        ]);

        $errorJson = json_encode([
            'status' => 'failure',
            'errorCode' => '5012',
            'errorMessage' => 'Taksit secenegi gecersizdir',
            'errorGroup' => 'FIELD_VALIDATION',
            'locale' => 'tr',
            'systemTime' => 1719000000,
        ]);

        $this->iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn($errorJson);

        /** @var Response $response */
        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5012', $response->getCode());
        $this->assertStringContainsString('Taksit', $response->getMessage() ?? '');
    }

    // ---------------------------------------------------------------
    //  sendData() – 3DS (ThreedsInitialize)
    // ---------------------------------------------------------------

    public function testSendDataSecure3dCallsThreedsInitialize(): void
    {
        $this->initializeRequest([
            'secure3d' => true,
            'returnUrl' => 'https://example.com/callback',
            'apiKey' => 'test-api-key',
            'secretKey' => 'test-secret-key',
            'baseUrl' => 'https://sandbox-api.iyzipay.com',
        ]);

        $jsonResponse = $this->buildThreedsInitializeJson();

        $this->iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('/payment/3dsecure/initialize'),
                $this->anything(),
                $this->anything()
            )
            ->willReturn($jsonResponse);

        /** @var RedirectResponse $response */
        $response = $this->request->send();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->isSuccessful());
        $this->assertNotNull($response->getHtmlContent());
        $this->assertStringContainsString('iyzipay-checkout-form', $response->getHtmlContent() ?? '');
    }

    public function testSendDataSecure3dWithoutCallbackUrlStillWorks(): void
    {
        $this->initializeRequest([
            'secure3d' => true,
            'returnUrl' => null,
            'apiKey' => 'test-api-key',
            'secretKey' => 'test-secret-key',
            'baseUrl' => 'https://sandbox-api.iyzipay.com',
        ]);

        $jsonResponse = $this->buildThreedsInitializeJson();

        $this->iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn($jsonResponse);

        /** @var RedirectResponse $response */
        $response = $this->request->send();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isRedirect());
        $this->assertNotNull($response->getHtmlContent());
    }

    public function testSendDataSecure3dStoresErrorResult(): void
    {
        $this->initializeRequest([
            'secure3d' => true,
            'returnUrl' => 'https://example.com/callback',
            'apiKey' => 'test-api-key',
            'secretKey' => 'test-secret-key',
            'baseUrl' => 'https://sandbox-api.iyzipay.com',
        ]);

        $errorJson = json_encode([
            'status' => 'failure',
            'errorCode' => '5001',
            'errorMessage' => 'Genel hata',
            'errorGroup' => 'SYSTEM',
            'locale' => 'tr',
            'systemTime' => 1719000000,
        ]);

        $this->iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn($errorJson);

        /** @var RedirectResponse $response */
        $response = $this->request->send();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getHtmlContent());
        $this->assertSame('5001', $response->getCode());
    }

    // ---------------------------------------------------------------
    //  posOrderId & paymentSource
    // ---------------------------------------------------------------

    public function testGetDataIncludesPosOrderIdWhenSet(): void
    {
        $this->initializeRequest([
            'posOrderId' => 'pos_order_456',
        ]);

        $data = $this->request->getData();

        $this->assertSame('pos_order_456', $data['posOrderId']);
    }

    public function testGetDataPosOrderIdDefaultsToEmptyString(): void
    {
        $this->initializeRequest();

        $data = $this->request->getData();

        $this->assertSame('', $data['posOrderId']);
    }

    public function testGetDataIncludesPaymentSourceWhenSet(): void
    {
        $this->initializeRequest([
            'paymentSource' => 'WEB',
        ]);

        $data = $this->request->getData();

        $this->assertSame('WEB', $data['paymentSource']);
    }

    public function testGetDataPaymentSourceDefaultsToEmptyString(): void
    {
        $this->initializeRequest();

        $data = $this->request->getData();

        $this->assertSame('', $data['paymentSource']);
    }
}
