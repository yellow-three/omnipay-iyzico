<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\ListCardsRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ListCardsRequestTest extends TestCase
{
    private ListCardsRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new ListCardsRequest($httpClient, $httpRequest);

        $this->request->setApiKey('test-api-key');
        $this->request->setSecretKey('test-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);

        parent::tearDown();
    }

    public function testGetDataReturnsCorrectArrayStructure(): void
    {
        $this->request->setCardUserKey('card_user_key_123');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertIsArray($data);
        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('card_user_key_123', $data['cardUserKey']);
    }

    public function testGetDataThrowsExceptionWhenCardUserKeyMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The cardUserKey parameter is required');

        $this->request->getData();
    }

    public function testSendDataCallsCardListRetrieveAndReturnsResponse(): void
    {
        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'success',
                'cardUserKey' => 'card_user_key_123',
                'cardDetails' => [
                    [
                        'cardToken' => 'card_token_001',
                        'cardUserKey' => 'card_user_key_123',
                        'cardAlias' => 'My Card 1',
                        'binNumber' => '552879',
                        'lastFourDigits' => '0008',
                        'cardType' => 'CREDIT_CARD',
                        'cardAssociation' => 'MASTER_CARD',
                        'cardFamily' => 'Axess',
                        'cardBankCode' => 10,
                        'cardBankName' => 'Akbank',
                    ],
                    [
                        'cardToken' => 'card_token_002',
                        'cardUserKey' => 'card_user_key_123',
                        'cardAlias' => 'My Card 2',
                        'binNumber' => '454359',
                        'lastFourDigits' => '0006',
                        'cardType' => 'CREDIT_CARD',
                        'cardAssociation' => 'VISA',
                        'cardFamily' => 'Maximum',
                        'cardBankCode' => 64,
                        'cardBankName' => 'İş Bankası',
                    ],
                ],
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $this->request->setCardUserKey('card_user_key_123');
        $this->request->setLocale('TR');

        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('success', $response->getStatus());
        $responseData = (fn() => $this->data)->call($response);
        $this->assertSame('card_user_key_123', $responseData['cardUserKey']);
        $this->assertIsArray($responseData['cardDetails']);
        $this->assertCount(2, $responseData['cardDetails']);
    }

    public function testSendDataHandlesListCardsError(): void
    {
        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '1004',
                'errorMessage' => 'Card user not found',
                'errorGroup' => 'CARD_STORAGE',
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $this->request->setCardUserKey('invalid_user_key');
        $this->request->setLocale('TR');

        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('failure', $response->getStatus());
        $this->assertSame('1004', $response->getCode());
        $this->assertStringContainsString('Card user not found', $response->getMessage());
    }

    public function testSendDataAppliesSignatureWithListCardsEndpoint(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Message/ListCardsRequest.php');
        $this->assertStringContainsString("'list-cards'", $source);
    }
}
