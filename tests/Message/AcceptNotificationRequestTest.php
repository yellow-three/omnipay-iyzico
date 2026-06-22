<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Iyzico\Message\AcceptNotificationRequest;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Http\ClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class AcceptNotificationRequestTest extends TestCase
{
    private AcceptNotificationRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new AcceptNotificationRequest($httpClient, $httpRequest);
    }

    public function testTransactionReferenceReturnsPaymentId(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'conversationData' => 'conv_data_123',
            'mdStatus' => 1,
            'status' => 'success',
        ]);

        $this->assertSame('pay_123', $this->request->getTransactionReference());
    }

    public function testTransactionReferenceReturnsNullWhenNoPaymentId(): void
    {
        $this->request->initialize([
            'status' => 'success',
        ]);

        $this->assertNull($this->request->getTransactionReference());
    }

    public function testTransactionStatusCompletedFor3dsSuccess(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'mdStatus' => 1,
            'status' => 'success',
        ]);

        $this->assertSame(NotificationInterface::STATUS_COMPLETED, $this->request->getTransactionStatus());
    }

    public function testTransactionStatusFailedFor3dsMdStatus0(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'mdStatus' => 0,
            'status' => 'success',
        ]);

        $this->assertSame(NotificationInterface::STATUS_FAILED, $this->request->getTransactionStatus());
    }

    public function testTransactionStatusFailedFor3dsMdStatus4(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'mdStatus' => 4,
            'status' => 'failure',
        ]);

        $this->assertSame(NotificationInterface::STATUS_FAILED, $this->request->getTransactionStatus());
    }

    public function testTransactionStatusCompletedForNon3dsSuccess(): void
    {
        $this->request->initialize([
            'status' => 'success',
        ]);

        $this->assertSame(NotificationInterface::STATUS_COMPLETED, $this->request->getTransactionStatus());
    }

    public function testTransactionStatusPending(): void
    {
        $this->request->initialize([
            'status' => 'pending',
        ]);

        $this->assertSame(NotificationInterface::STATUS_PENDING, $this->request->getTransactionStatus());
    }

    public function testTransactionStatusFailedForFailure(): void
    {
        $this->request->initialize([
            'status' => 'failure',
            'errorCode' => '5001',
            'errorMessage' => 'Payment failed',
        ]);

        $this->assertSame(NotificationInterface::STATUS_FAILED, $this->request->getTransactionStatus());
    }

    public function testTransactionStatusFailedForEmptyData(): void
    {
        $this->request->initialize([]);

        $this->assertSame(NotificationInterface::STATUS_FAILED, $this->request->getTransactionStatus());
    }

    public function testMessageReturnsErrorMessage(): void
    {
        $this->request->initialize([
            'status' => 'failure',
            'errorCode' => '5001',
            'errorMessage' => 'Payment failed',
        ]);

        $this->assertStringContainsString('Payment failed', $this->request->getMessage());
        $this->assertStringContainsString('5001', $this->request->getMessage());
    }

    public function testMessageReturnsSuccessMessage(): void
    {
        $this->request->initialize([
            'mdStatus' => 1,
            'status' => 'success',
            'paymentId' => 'pay_123',
        ]);

        $this->assertSame('Payment completed successfully', $this->request->getMessage());
    }

    public function testMessageReturnsPendingMessage(): void
    {
        $this->request->initialize([
            'status' => 'pending',
        ]);

        $this->assertSame('Payment is pending', $this->request->getMessage());
    }

    public function testMessageReturnsUnknownForNoStatus(): void
    {
        $this->request->initialize([]);

        $this->assertStringContainsString('Unknown', $this->request->getMessage() ?? '');
    }

    public function testNotificationInterfaceImplementation(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'mdStatus' => 1,
            'status' => 'success',
        ]);

        $this->assertInstanceOf(NotificationInterface::class, $this->request);
        $this->assertSame('pay_123', $this->request->getTransactionReference());
        $this->assertSame(NotificationInterface::STATUS_COMPLETED, $this->request->getTransactionStatus());
        $this->assertSame('Payment completed successfully', $this->request->getMessage());
    }

    public function testSendReturnsSelf(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'mdStatus' => 1,
            'status' => 'success',
        ]);

        $result = $this->request->send();

        $this->assertSame($this->request, $result);
        $this->assertSame('pay_123', $result->getTransactionReference());
    }
}
