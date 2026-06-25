<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Iyzico\Message\Subscription\CreateCustomerRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class CreateCustomerRequestTest extends TestCase
{
    private CreateCustomerRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new CreateCustomerRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setEmail('john@example.com');
        $this->request->setGsmNumber('+905551112233');
        $this->request->setName('John');
        $this->request->setSurname('Doe');
        $this->request->setIdentityNumber('12345678901');
        $this->request->setContactEmail('contact@example.com');
        $this->request->setContactGsmNumber('+905559998877');
        $this->request->setBillingAddress('Billing St. No:1');
        $this->request->setBillingCity('Istanbul');
        $this->request->setBillingDistrict('Kadikoy');
        $this->request->setBillingCountry('Turkey');
        $this->request->setBillingZipCode('34700');
        $this->request->setBillingContactName('John Doe');
        $this->request->setShippingAddress('Shipping St. No:2');
        $this->request->setShippingCity('Ankara');
        $this->request->setShippingDistrict('Cankaya');
        $this->request->setShippingCountry('Turkey');
        $this->request->setShippingZipCode('06500');
        $this->request->setShippingContactName('John Doe');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('john@example.com', $data['email']);
        $this->assertSame('+905551112233', $data['gsmNumber']);
        $this->assertSame('John', $data['name']);
        $this->assertSame('Doe', $data['surname']);
        $this->assertSame('12345678901', $data['identityNumber']);
        $this->assertSame('contact@example.com', $data['contactEmail']);
        $this->assertSame('+905559998877', $data['contactGsmNumber']);
        $this->assertSame('Billing St. No:1', $data['billingAddress']);
        $this->assertSame('Istanbul', $data['billingCity']);
        $this->assertSame('Kadikoy', $data['billingDistrict']);
        $this->assertSame('Turkey', $data['billingCountry']);
        $this->assertSame('34700', $data['billingZipCode']);
        $this->assertSame('John Doe', $data['billingContactName']);
        $this->assertSame('Shipping St. No:2', $data['shippingAddress']);
        $this->assertSame('Ankara', $data['shippingCity']);
        $this->assertSame('Cankaya', $data['shippingDistrict']);
        $this->assertSame('Turkey', $data['shippingCountry']);
        $this->assertSame('06500', $data['shippingZipCode']);
        $this->assertSame('John Doe', $data['shippingContactName']);
    }

    public function testGetDataThrowsWhenEmailMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The email parameter is required');

        $this->request->getData();
    }

    public function testSetAndGetEmail(): void
    {
        $result = $this->request->setEmail('test@example.com');
        $this->assertSame($this->request, $result);
        $this->assertSame('test@example.com', $this->request->getEmail());
    }

    public function testSendDataReturnsResponse(): void
    {
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
        $this->request->setEmail('fail@example.com');
        $this->request->setName('John');
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
                'errorCode' => '5001',
                'errorMessage' => 'Customer already exists',
                'errorGroup' => 'CUSTOMER_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5001', $response->getCode());
        $this->assertStringContainsString('Customer already exists', $response->getMessage());
    }
}
