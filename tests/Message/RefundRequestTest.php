<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\RefundRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class RefundRequestTest extends TestCase
{
    private RefundRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new RefundRequest($httpClient, $httpRequest);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setPaymentTransactionId('tx_123');
        $this->request->setConversationId('conv_123');
        $this->request->setAmount('50.00');
        $this->request->setCurrency('TRY');
        $this->request->setLocale('TR');
        $this->request->setReason('buyer request');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('tx_123', $data['paymentTransactionId']);
        $this->assertSame('50.00', $data['price']);
        $this->assertSame('TRY', $data['currency']);
        $this->assertSame('buyer request', $data['reason']);
    }

    public function testGetDataThrowsWhenPaymentTransactionIdMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The paymentTransactionId parameter is required');

        $this->request->setConversationId('conv_123');
        $this->request->setAmount('50.00');

        $this->request->getData();
    }

    public function testGetDataThrowsWhenConversationIdMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The conversationId parameter is required');

        $this->request->setPaymentTransactionId('tx_123');
        $this->request->setAmount('50.00');

        $this->request->getData();
    }

    public function testGetDataThrowsWhenAmountMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The amount parameter is required');

        $this->request->setPaymentTransactionId('tx_123');
        $this->request->setConversationId('conv_123');

        $this->request->getData();
    }

    public function testGetPaymentTransactionId(): void
    {
        $this->request->setPaymentTransactionId('tx_456');

        $this->assertSame('tx_456', $this->request->getPaymentTransactionId());
    }

    public function testSetPaymentTransactionIdReturnsSelf(): void
    {
        $result = $this->request->setPaymentTransactionId('tx_456');

        $this->assertSame($this->request, $result);
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
        $this->request->setPaymentTransactionId('tx_123');
        $this->request->setConversationId('conv_123');
        $this->request->setAmount('50.00');
        $this->request->setLocale('TR');

        $data = $this->request->getData();

        $this->assertSame('buyer request', $data['reason']);
    }
}
