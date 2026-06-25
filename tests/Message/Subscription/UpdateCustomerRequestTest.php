<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Iyzico\Message\Subscription\UpdateCustomerRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class UpdateCustomerRequestTest extends TestCase
{
    private UpdateCustomerRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new UpdateCustomerRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setCustomerReferenceCode('cust_ref_001');
        $this->request->setEmail('john@example.com');
        $this->request->setGsmNumber('+905551112233');
        $this->request->setName('John');
        $this->request->setSurname('Doe');
        $this->request->setIdentityNumber('12345678901');
        $this->request->setBillingAddress('Billing St. No:1');
        $this->request->setBillingCity('Istanbul');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('cust_ref_001', $data['customerReferenceCode']);
        $this->assertSame('john@example.com', $data['email']);
        $this->assertSame('+905551112233', $data['gsmNumber']);
        $this->assertSame('John', $data['name']);
        $this->assertSame('Doe', $data['surname']);
        $this->assertSame('12345678901', $data['identityNumber']);
    }

    public function testGetDataThrowsWhenCustomerReferenceCodeMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The customerReferenceCode parameter is required');

        $this->request->getData();
    }

    public function testSetAndGetCustomerReferenceCode(): void
    {
        $result = $this->request->setCustomerReferenceCode('cust_ref_002');
        $this->assertSame($this->request, $result);
        $this->assertSame('cust_ref_002', $this->request->getCustomerReferenceCode());
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setCustomerReferenceCode('cust_ref_001');
        $this->request->setEmail('john@example.com');
        $this->request->setName('John');
        $this->request->setSurname('Doe');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
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
                'conversationId' => 'conv_123',
                'referenceCode' => 'cust_ref_001',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setCustomerReferenceCode('cust_ref_not_found');
        $this->request->setEmail('john@example.com');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_fail');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '5002',
                'errorMessage' => 'Customer not found',
                'errorGroup' => 'CUSTOMER_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5002', $response->getCode());
        $this->assertStringContainsString('Customer not found', $response->getMessage());
    }
}
