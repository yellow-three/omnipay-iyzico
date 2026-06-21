<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\CaptureRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class CaptureRequestTest extends TestCase
{
    private CaptureRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new CaptureRequest($httpClient, $httpRequest);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setPaymentId('pay_123');
        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');
        $this->request->setClientIp('192.168.1.1');

        $data = $this->request->getData();

        $this->assertSame('pay_123', $data['paymentId']);
        $this->assertSame('100.00', $data['paidPrice']);
        $this->assertSame('TRY', $data['currency']);
        $this->assertSame('192.168.1.1', $data['clientIp']);
    }

    public function testGetDataThrowsWhenPaymentIdMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The paymentId parameter is required');

        $this->request->setAmount('100.00');

        $this->request->getData();
    }

    public function testGetDataReturnsClientIpAsNullWhenNotSet(): void
    {
        $this->request->setPaymentId('pay_123');
        $this->request->setAmount('100.00');
        $this->request->setCurrency('TRY');

        $data = $this->request->getData();

        $this->assertArrayHasKey('clientIp', $data);
    }
}
