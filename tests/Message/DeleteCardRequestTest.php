<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\DeleteCardRequest;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class DeleteCardRequestTest extends TestCase
{
    private DeleteCardRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new DeleteCardRequest($httpClient, $httpRequest);

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
        $this->request->setCardToken('card_token_123');
        $this->request->setCardUserKey('card_user_key_123');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertIsArray($data);
        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('card_token_123', $data['cardToken']);
        $this->assertSame('card_user_key_123', $data['cardUserKey']);
    }

    public function testGetDataThrowsExceptionWhenCardTokenMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The cardToken parameter is required');

        $this->request->setCardUserKey('card_user_key_123');
        $this->request->getData();
    }

    public function testGetDataThrowsExceptionWhenCardUserKeyMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The cardUserKey parameter is required');

        $this->request->setCardToken('card_token_123');
        $this->request->getData();
    }

    public function testSendDataCallsCardDeleteAndReturnsResponse(): void
    {
        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('delete')
            ->willReturn(json_encode([
                'status' => 'success',
                'cardToken' => 'card_token_123',
                'cardUserKey' => 'card_user_key_123',
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $this->request->setCardToken('card_token_123');
        $this->request->setCardUserKey('card_user_key_123');
        $this->request->setLocale('TR');

        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('success', $response->getStatus());
        $responseData = (fn() => $this->data)->call($response);
        $this->assertSame('card_token_123', $responseData['cardToken']);
        $this->assertSame('card_user_key_123', $responseData['cardUserKey']);
    }

    public function testSendDataHandlesCardDeleteError(): void
    {
        $iyzicoHttpClient = $this->createMock(HttpClient::class);
        $iyzicoHttpClient
            ->expects($this->once())
            ->method('delete')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '1006',
                'errorMessage' => 'Card not found',
                'errorGroup' => 'CARD_STORAGE',
            ]));
        ApiResource::setHttpClient($iyzicoHttpClient);

        $this->request->setCardToken('non_existent_token');
        $this->request->setCardUserKey('card_user_key_123');
        $this->request->setLocale('TR');

        $response = $this->request->send();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('failure', $response->getStatus());
        $this->assertSame('1006', $response->getCode());
        $this->assertStringContainsString('Card not found', $response->getMessage());
    }
}
