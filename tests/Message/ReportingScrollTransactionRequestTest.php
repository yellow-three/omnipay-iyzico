<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\ReportingScrollTransactionRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ReportingScrollTransactionRequestTest extends TestCase
{
    private ReportingScrollTransactionRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new ReportingScrollTransactionRequest($httpClient, $httpRequest);
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
        $this->request->setLastId('last_123');
        $this->request->setDocumentScrollVoSortingOrder('ASC');

        $data = $this->request->getData();

        $this->assertSame('2024-01-01', $data['transactionDate']);
        $this->assertSame('last_123', $data['lastId']);
        $this->assertSame('ASC', $data['documentScrollVoSortingOrder']);
    }

    public function testGetLastId(): void
    {
        $this->request->setLastId('last_123');
        $this->assertSame('last_123', $this->request->getLastId());
    }

    public function testSetLastIdReturnsSelf(): void
    {
        $result = $this->request->setLastId('last_123');
        $this->assertSame($this->request, $result);
    }

    public function testGetDocumentScrollVoSortingOrder(): void
    {
        $this->request->setDocumentScrollVoSortingOrder('ASC');
        $this->assertSame('ASC', $this->request->getDocumentScrollVoSortingOrder());
    }

    public function testSetDocumentScrollVoSortingOrderReturnsSelf(): void
    {
        $result = $this->request->setDocumentScrollVoSortingOrder('DESC');
        $this->assertSame($this->request, $result);
    }
}