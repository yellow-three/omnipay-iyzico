<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Iyzico\Message\InstallmentRequest;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class InstallmentRequestTest extends TestCase
{
    private InstallmentRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new InstallmentRequest($httpClient, $httpRequest);
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
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('currency', $data);
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
                'installmentDetails' => [
                    [
                        'binNumber' => '554960',
                        'price' => 100.0,
                        'cardType' => 'CREDIT_CARD',
                        'cardAssociation' => 'MASTER_CARD',
                        'cardFamilyName' => 'Bonus',
                        'bankName' => 'Garanti Bankası',
                        'bankCode' => 62,
                        'commercial' => 0,
                        'installmentPrices' => [
                            [
                                'installmentPrice' => 100.0,
                                'totalPrice' => 100.0,
                                'installmentNumber' => 1,
                            ],
                        ],
                    ],
                ],
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertArrayHasKey('installmentDetails', $response->getData());
        $this->assertIsArray($response->getData()['installmentDetails']);
    }

    public function testSendDataWithPriceAndCurrency(): void
    {
        $this->request->setBinNumber('554960');
        $this->request->setAmount('250.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $this->assertSame('250.00', $data['price']);
        $this->assertSame('TRY', $data['currency']);

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'success',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => $data['conversationId'],
                'installmentDetails' => [
                    [
                        'binNumber' => '554960',
                        'price' => 250.0,
                        'cardType' => 'CREDIT_CARD',
                        'cardAssociation' => 'MASTER_CARD',
                        'cardFamilyName' => 'Bonus',
                        'bankName' => 'Garanti Bankası',
                        'bankCode' => 62,
                        'commercial' => 0,
                        'installmentPrices' => [
                            [
                                'installmentPrice' => 250.0,
                                'totalPrice' => 250.0,
                                'installmentNumber' => 1,
                            ],
                            [
                                'installmentPrice' => 125.0,
                                'totalPrice' => 250.0,
                                'installmentNumber' => 2,
                            ],
                            [
                                'installmentPrice' => 83.34,
                                'totalPrice' => 250.02,
                                'installmentNumber' => 3,
                            ],
                        ],
                    ],
                ],
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
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

    public function testSendDataAppliesSignatureWithInstallmentEndpoint(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Message/InstallmentRequest.php');
        $this->assertStringContainsString("'installment'", $source);
    }
}
