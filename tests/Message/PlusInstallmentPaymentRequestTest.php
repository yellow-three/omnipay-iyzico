<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\PlusInstallmentPaymentRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class PlusInstallmentPaymentRequestTest extends TestCase
{
    private PlusInstallmentPaymentRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new PlusInstallmentPaymentRequest($httpClient, $httpRequest);

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

    public function testGetDataReturnsAllParameters(): void
    {
        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setAmount('200.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('EN');
        $this->request->setConversationId('conv_456');
        $this->request->setInstallment(2);
        $this->request->setPaymentChannel('WEB');
        $this->request->setPaymentGroup('PRODUCT');
        $this->request->setBasketId('basket_123');
        $this->request->setConnectorName('bank_connector');
        $this->request->setPlusInstallmentUsage(1);

        $data = $this->request->getData();

        $this->assertIsArray($data);
        $this->assertSame('EN', $data['locale']);
        $this->assertSame('conv_456', $data['conversationId']);
        $this->assertSame('200.00', $data['price']);
        $this->assertSame('200.00', $data['paidPrice']);
        $this->assertSame('TRY', $data['currency']);
        $this->assertSame(2, $data['installment']);
        $this->assertSame('WEB', $data['paymentChannel']);
        $this->assertSame('PRODUCT', $data['paymentGroup']);
        $this->assertSame('basket_123', $data['basketId']);
        $this->assertSame('bank_connector', $data['connectorName']);
        $this->assertSame(1, $data['plusInstallmentUsage']);
        $this->assertInstanceOf(CreditCard::class, $data['card']);
    }

    public function testGetDataThrowsWhenAmountMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->getData();
    }

    public function testGetDataThrowsWhenCardMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->setAmount('100.00');
        $this->request->getData();
    }

    public function testGetBasketId(): void
    {
        $this->request->setBasketId('basket_test');
        $this->assertSame('basket_test', $this->request->getBasketId());
    }

    public function testSetBasketIdReturnsSelf(): void
    {
        $result = $this->request->setBasketId('basket_test');
        $this->assertSame($this->request, $result);
    }

    public function testGetConnectorName(): void
    {
        $this->request->setConnectorName('test_connector');
        $this->assertSame('test_connector', $this->request->getConnectorName());
    }

    public function testSetConnectorNameReturnsSelf(): void
    {
        $result = $this->request->setConnectorName('test_connector');
        $this->assertSame($this->request, $result);
    }

    public function testGetPlusInstallmentUsage(): void
    {
        $this->request->setPlusInstallmentUsage(1);
        $this->assertSame(1, $this->request->getPlusInstallmentUsage());
    }

    public function testSetPlusInstallmentUsageReturnsSelf(): void
    {
        $result = $this->request->setPlusInstallmentUsage(1);
        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'success',
                'paymentId' => 'pay_plus_123',
                'conversationId' => 'conv_456',
                'price' => '200.00',
                'paidPrice' => '200.00',
                'installment' => 2,
                'currency' => 'TRY',
                'paymentStatus' => 'SUCCESS',
                'basketId' => 'basket_123',
                'connectorName' => 'bank_connector',
                'plusInstallmentUsage' => 1,
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setAmount('200.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setPaymentChannel('WEB');
        $this->request->setPaymentGroup('PRODUCT');

        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('pay_plus_123', $response->getPaymentId());
        $this->assertSame('success', $response->getStatus());
        $this->assertSame('conv_456', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '5001',
                'errorMessage' => 'Invalid payment',
                'conversationId' => 'conv_456',
                'price' => '0.00',
                'paidPrice' => '0.00',
                'currency' => 'TRY',
                'installment' => 1,
                'basketId' => '',
                'connectorName' => '',
                'plusInstallmentUsage' => 0,
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setAmount('200.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setPaymentChannel('WEB');
        $this->request->setPaymentGroup('PRODUCT');

        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('failure', $response->getStatus());
    }
}
