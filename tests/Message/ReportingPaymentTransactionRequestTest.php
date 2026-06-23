<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\ReportingPaymentTransactionRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ReportingPaymentTransactionRequestTest extends TestCase
{
    private ReportingPaymentTransactionRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new ReportingPaymentTransactionRequest($httpClient, $httpRequest);
    }

    public function testGetDataThrowsWhenTransactionDateMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The transactionDate parameter is required');

        $this->request->getData();
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setTransactionDate('2024-01-01');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('2024-01-01', $data['transactionDate']);
        $this->assertSame('conv_123', $data['conversationId']);
    }

    public function testGetDataWithPageParameter(): void
    {
        $this->request->setTransactionDate('2024-01-01');
        $this->request->setPage(2);

        $data = $this->request->getData();

        $this->assertSame(2, $data['page']);
    }

    public function testGetTransactionDate(): void
    {
        $this->request->setTransactionDate('2024-06-23');
        $this->assertSame('2024-06-23', $this->request->getTransactionDate());
    }

    public function testSetTransactionDateReturnsSelf(): void
    {
        $result = $this->request->setTransactionDate('2024-06-23');
        $this->assertSame($this->request, $result);
    }

    public function testGetPageReturnsZeroWhenNotSet(): void
    {
        $this->assertSame(0, $this->request->getPage());
    }

    public function testSetPageReturnsSelf(): void
    {
        $result = $this->request->setPage(5);
        $this->assertSame($this->request, $result);
        $this->assertSame(5, $this->request->getPage());
    }
}