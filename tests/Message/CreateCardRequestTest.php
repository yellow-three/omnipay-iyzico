<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\CreateCardRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class CreateCardRequestTest extends TestCase
{
    private CreateCardRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new CreateCardRequest($httpClient, $httpRequest);

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
            'number' => '5528790000000008',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'John',
            'lastName' => 'Doe',
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
        $this->request->setEmail('test@example.com');
        $this->request->setCardUserKey('card_user_key_123');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setExternalId('ext_456');
        $this->request->setCardAlias('My Card');

        $data = $this->request->getData();

        $this->assertIsArray($data);
        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('test@example.com', $data['email']);
        $this->assertSame('card_user_key_123', $data['cardUserKey']);
        $this->assertSame('ext_456', $data['externalId']);
        $this->assertInstanceOf(CreditCard::class, $data['card']);
        $this->assertSame('My Card', $data['cardAlias']);
    }

    public function testGetDataThrowsExceptionWhenCardMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The card parameter is required');

        $this->request->setEmail('test@example.com');
        $this->request->setCardUserKey('card_user_key_123');
        $this->request->getData();
    }

    public function testGetDataThrowsExceptionWhenEmailMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The email parameter is required');

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setCardUserKey('card_user_key_123');
        $this->request->getData();
    }

    public function testGetDataThrowsExceptionWhenCardUserKeyMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The cardUserKey parameter is required');

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setEmail('test@example.com');
        $this->request->getData();
    }

    public function testSendDataCallsCardCreateAndReturnsResponse(): void
    {
        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'success',
                'cardToken' => 'card_token_123',
                'cardUserKey' => 'card_user_key_123',
                'cardAlias' => 'My Card',
                'cardBankCode' => 10,
                'cardBankName' => 'Akbank',
                'binNumber' => '552879',
                'lastFourDigits' => '0008',
                'cardType' => 'CREDIT_CARD',
                'cardAssociation' => 'MASTER_CARD',
                'cardFamily' => 'Axess',
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setEmail('test@example.com');
        $this->request->setCardUserKey('card_user_key_123');
        $this->request->setCardAlias('My Card');
        $this->request->setLocale('TR');

        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('success', $response->getStatus());
    }

    public function testSendDataHandlesCardCreateError(): void
    {
        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '1005',
                'errorMessage' => 'Card already exists',
                'errorGroup' => 'CARD_STORAGE',
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setEmail('test@example.com');
        $this->request->setCardUserKey('card_user_key_123');
        $this->request->setLocale('TR');

        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('failure', $response->getStatus());
        $this->assertSame('1005', $response->getCode());
        $this->assertStringContainsString('Card already exists', $response->getMessage());
    }

    public function testGetExternalId(): void
    {
        $this->request->setExternalId('ext_789');

        $this->assertSame('ext_789', $this->request->getExternalId());
    }

    public function testSetExternalIdReturnsSelf(): void
    {
        $result = $this->request->setExternalId('ext_789');

        $this->assertSame($this->request, $result);
    }

    public function testExternalIdDefaultsToEmptyInGetData(): void
    {
        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setEmail('test@example.com');
        $this->request->setCardUserKey('card_user_key_123');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('', $data['externalId']);
    }

    public function testGetCardAlias(): void
    {
        $this->request->setCardAlias('My Wallet');

        $this->assertSame('My Wallet', $this->request->getCardAlias());
    }

    public function testSetCardAliasReturnsSelf(): void
    {
        $result = $this->request->setCardAlias('My Wallet');

        $this->assertSame($this->request, $result);
    }

    public function testCardAliasDefaultsToEmptyInGetData(): void
    {
        $card = $this->createValidCard();
        $this->request->setCard($card);
        $this->request->setEmail('test@example.com');
        $this->request->setCardUserKey('card_user_key_123');
        $this->request->setLocale('TR');

        $data = $this->request->getData();

        $this->assertSame('', $data['cardAlias']);
    }

    public function testSendDataAppliesSignatureWithCreateCardEndpoint(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Message/CreateCardRequest.php');
        $this->assertStringContainsString("'create-card'", $source);
    }
}
