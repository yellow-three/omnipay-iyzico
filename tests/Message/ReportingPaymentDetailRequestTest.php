<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\ReportingPaymentDetailRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ReportingPaymentDetailRequestTest extends TestCase
{
    private ReportingPaymentDetailRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new ReportingPaymentDetailRequest($httpClient, $httpRequest);
    }

    public function testGetDataDoesNotThrowWhenPaymentIdOrConversationIdMissing(): void
    {
        $this->request->setLocale('TR');
        $data = $this->request->getData();
        $this->assertSame('', $data['paymentId']);
        $this->assertNull($data['paymentConversationId']);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setPaymentId('pay_123');
        $this->request->setPaymentConversationId('conv_abc');
        $this->request->setConversationId('conv_123');
        $this->request->setLocale('TR');

        $data = $this->request->getData();

        $this->assertSame('pay_123', $data['paymentId']);
        $this->assertSame('conv_abc', $data['paymentConversationId']);
    }

    public function testGetPaymentId(): void
    {
        $this->request->setPaymentId('pay_123');
        $this->assertSame('pay_123', $this->request->getPaymentId());
    }

    public function testSetPaymentIdReturnsSelf(): void
    {
        $result = $this->request->setPaymentId('pay_456');
        $this->assertSame($this->request, $result);
    }

    public function testGetPaymentConversationId(): void
    {
        $this->request->setPaymentConversationId('conv_abc');
        $this->assertSame('conv_abc', $this->request->getPaymentConversationId());
    }

    public function testSetPaymentConversationIdReturnsSelf(): void
    {
        $result = $this->request->setPaymentConversationId('conv_xyz');
        $this->assertSame($this->request, $result);
    }
}