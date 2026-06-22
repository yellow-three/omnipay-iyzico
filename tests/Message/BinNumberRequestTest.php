<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Iyzico\Message\BinNumberRequest;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class BinNumberRequestTest extends TestCase
{
    private BinNumberRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new BinNumberRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
        parent::tearDown();
    }

    public function testGetDataReturnsAllParameters(): void
    {
        $this->request->setBinNumber('554960');
        $this->request->setLocale('TR');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('554960', $data['binNumber']);
        $this->assertArrayHasKey('conversationId', $data);
    }

    public function testGetDataThrowsWhenBinNumberMissing(): void
    {
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testSendDataReturnsSuccessfulResponse(): void
    {
        $this->request->setBinNumber('554960');
        $this->request->setLocale('TR');
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
                'systemTime' => '1458545234852',
                'conversationId' => $data['conversationId'],
                'binNumber' => '554960',
                'cardType' => 'CREDIT_CARD',
                'cardAssociation' => 'MASTER_CARD',
                'cardFamily' => 'Bonus',
                'bankName' => 'Garanti Bankası',
                'bankCode' => 62,
                'commercial' => 0,
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('554960', $response->getData()['binNumber']);
        $this->assertSame('CREDIT_CARD', $response->getData()['cardType']);
        $this->assertSame('MASTER_CARD', $response->getData()['cardAssociation']);
        $this->assertSame('Bonus', $response->getData()['cardFamily']);
        $this->assertSame('Garanti Bankası', $response->getData()['bankName']);
        $this->assertSame(62, $response->getData()['bankCode']);
        $this->assertSame(0, $response->getData()['commercial']);
    }

    public function testSendDataWithFailedResponse(): void
    {
        $this->request->setBinNumber('000000');
        $this->request->setLocale('TR');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '1001',
                'errorMessage' => 'Bin number not found',
                'errorGroup' => 'BIN_NOT_FOUND',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => $data['conversationId'],
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('1001', $response->getCode());
        $this->assertStringContainsString('Bin number not found', $response->getMessage());
    }
}
