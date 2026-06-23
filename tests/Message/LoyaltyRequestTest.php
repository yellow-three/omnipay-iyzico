<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\LoyaltyRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class LoyaltyRequestTest extends TestCase
{
    private LoyaltyRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new LoyaltyRequest($httpClient, $httpRequest);

        $this->request->setApiKey('test-api-key');
        $this->request->setSecretKey('test-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');
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
        $this->request->setCurrency('TRY');
        $this->request->setLocale('EN');
        $this->request->setConversationId('conv_loyalty_123');

        $data = $this->request->getData();

        $this->assertIsArray($data);
        $this->assertSame('EN', $data['locale']);
        $this->assertSame('conv_loyalty_123', $data['conversationId']);
        $this->assertSame('TRY', $data['currency']);
        $this->assertInstanceOf(CreditCard::class, $data['card']);
    }

    public function testGetDataThrowsWhenCardMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testSendDataReturnsResponse(): void
    {
        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'success',
                'conversationId' => 'conv_loyalty_123',
                'points' => '100',
                'amount' => '10.00',
                'cardBank' => 'Akbank',
                'cardFamily' => 'Axess',
                'currency' => 'TRY',
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_loyalty_123');

        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_loyalty_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '5002',
                'errorMessage' => 'Card not found',
                'conversationId' => 'conv_loyalty_123',
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_loyalty_123');

        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('failure', $response->getStatus());
    }
}
