<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\VoidRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class VoidRequestTest extends TestCase
{
    private VoidRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new VoidRequest($httpClient, $httpRequest);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setPaymentId('pay_123');
        $this->request->setConversationId('conv_123');
        $this->request->setLocale('TR');
        $this->request->setReason('buyer request');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('pay_123', $data['paymentId']);
        $this->assertSame('buyer request', $data['reason']);
    }

    public function testGetDataThrowsWhenPaymentIdMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The paymentId parameter is required');

        $this->request->setConversationId('conv_123');

        $this->request->getData();
    }

    public function testGetDataThrowsWhenConversationIdMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The conversationId parameter is required');

        $this->request->setPaymentId('pay_123');

        $this->request->getData();
    }

    public function testGetReason(): void
    {
        $this->request->setReason('chargeback');

        $this->assertSame('chargeback', $this->request->getReason());
    }

    public function testSetReasonReturnsSelf(): void
    {
        $result = $this->request->setReason('chargeback');

        $this->assertSame($this->request, $result);
    }

    public function testReasonDefaultsToBuyerRequestInGetData(): void
    {
        $this->request->setPaymentId('pay_123');
        $this->request->setConversationId('conv_123');
        $this->request->setLocale('TR');

        $data = $this->request->getData();

        $this->assertSame('buyer request', $data['reason']);
    }
}
