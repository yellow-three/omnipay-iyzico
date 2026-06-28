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

    public function testDirectFormatSuccess(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'iyziEventType' => 'DIRECT',
            'paymentConversationId' => 'conv_123',
            'status' => 'SUCCESS',
        ]);

        $this->assertSame(NotificationInterface::STATUS_COMPLETED, $this->request->getTransactionStatus());
        $this->assertSame('pay_123', $this->request->getTransactionReference());
        $this->assertStringContainsString('SUCCESS', $this->request->getMessage());
        $this->assertStringContainsString('DIRECT', $this->request->getMessage());
    }

    public function testDirectFormatFailure(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'iyziEventType' => 'DIRECT',
            'paymentConversationId' => 'conv_123',
            'status' => 'FAILURE',
        ]);

        $this->assertSame(NotificationInterface::STATUS_FAILED, $this->request->getTransactionStatus());
    }

    public function testDirectFormatInitThreedsIsPending(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'iyziEventType' => 'DIRECT',
            'paymentConversationId' => 'conv_123',
            'status' => 'INIT_THREEDS',
        ]);

        $this->assertSame(NotificationInterface::STATUS_PENDING, $this->request->getTransactionStatus());
    }

    public function testHppFormatSuccess(): void
    {
        $this->request->initialize([
            'token' => 'tok_123',
            'iyziPaymentId' => 'iyzi_123',
            'iyziEventType' => 'HPP',
            'paymentConversationId' => 'conv_123',
            'status' => 'SUCCESS',
        ]);

        $this->assertSame(NotificationInterface::STATUS_COMPLETED, $this->request->getTransactionStatus());
        $this->assertSame('iyzi_123', $this->request->getTransactionReference());
    }

    public function testHppFormatTransactionReferenceFallback(): void
    {
        $this->request->initialize([
            'token' => 'tok_123',
        ]);

        $this->assertSame('tok_123', $this->request->getTransactionReference());
    }

    public function testIsValidDirectWithValidSignature(): void
    {
        $secretKey = 'test_secret_key';
        $paymentId = 'pay_123';
        $iyziEventType = 'DIRECT';
        $paymentConversationId = 'conv_123';
        $status = 'SUCCESS';

        $expectedSignature = hash_hmac('sha256', $secretKey . $iyziEventType . $paymentId . $paymentConversationId . $status, $secretKey);

        // Call initialize BEFORE setSecretKey — Omnipay's parent::initialize() rebuilds the ParameterBag.
        $this->request->initialize([
            'paymentId' => $paymentId,
            'iyziEventType' => $iyziEventType,
            'paymentConversationId' => $paymentConversationId,
            'status' => $status,
            'signature' => $expectedSignature,
        ]);
        $this->request->setSecretKey($secretKey);

        $this->assertTrue($this->request->isValid());
    }

    public function testIsValidDirectWithInvalidSignature(): void
    {
        $secretKey = 'test_secret_key';
        $paymentId = 'pay_123';
        $iyziEventType = 'DIRECT';
        $paymentConversationId = 'conv_123';
        $status = 'SUCCESS';

        $this->request->initialize([
            'paymentId' => $paymentId,
            'iyziEventType' => $iyziEventType,
            'paymentConversationId' => $paymentConversationId,
            'status' => $status,
            'signature' => 'invalid_signature_value',
        ]);
        $this->request->setSecretKey($secretKey);

        $this->assertFalse($this->request->isValid());
    }

    public function testIsValidHppWithValidSignature(): void
    {
        $secretKey = 'test_secret_key';
        $iyziEventType = 'HPP_EVENT';
        $iyziPaymentId = 'iyzi_789';
        $token = 'tok_456';
        $paymentConversationId = 'conv_789';
        $status = 'SUCCESS';

        $expectedSignature = hash_hmac('sha256', $secretKey . $iyziEventType . $iyziPaymentId . $token . $paymentConversationId . $status, $secretKey);

        // Call initialize BEFORE setSecretKey — Omnipay's parent::initialize() rebuilds the ParameterBag.
        $this->request->initialize([
            'token' => $token,
            'iyziPaymentId' => $iyziPaymentId,
            'iyziEventType' => $iyziEventType,
            'paymentConversationId' => $paymentConversationId,
            'status' => $status,
            'signature' => $expectedSignature,
        ]);
        $this->request->setSecretKey($secretKey);

        $this->assertTrue($this->request->isValid());
    }

    public function testIsValidWithEmptySecretKeyReturnsFalse(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'iyziEventType' => 'DIRECT',
            'paymentConversationId' => 'conv_123',
            'status' => 'SUCCESS',
            'signature' => 'some_signature',
        ]);

        $this->assertFalse($this->request->isValid());
    }

    public function testEmptyDataReturnsStatusFailed(): void
    {
        $this->request->initialize([]);

        $this->assertSame(NotificationInterface::STATUS_FAILED, $this->request->getTransactionStatus());
    }

    public function testEmptyDataReturnsNoNotificationMessage(): void
    {
        $this->request->initialize([]);

        $this->assertSame('No notification data received', $this->request->getMessage());
    }

    public function testNotificationInterfaceImplementation(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'iyziEventType' => 'DIRECT',
            'paymentConversationId' => 'conv_123',
            'status' => 'SUCCESS',
        ]);

        $this->assertInstanceOf(NotificationInterface::class, $this->request);
    }

    public function testSendReturnsSelf(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_123',
            'status' => 'SUCCESS',
        ]);

        $result = $this->request->send();

        $this->assertSame($this->request, $result);
    }

    public function testInitOrderingDataAvailableAfterParentInitialize(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_init_123',
            'iyziEventType' => 'DIRECT',
            'paymentConversationId' => 'conv_init_123',
            'status' => 'SUCCESS',
            'customField' => 'custom_value',
        ]);

        // Data should be available immediately after initialize().
        // Fields with setters on AbstractRequest (paymentId, conversationId)
        // go to the ParameterBag; only notification-specific fields land in $this->data.
        $data = $this->request->getData();

        $this->assertSame('DIRECT', $data['iyziEventType']);
        $this->assertSame('SUCCESS', $data['status']);
        $this->assertSame('custom_value', $data['customField']);
    }

    public function testInitNoDataLossOnCustomParams(): void
    {
        $this->request->initialize([
            'paymentId' => 'pay_nodrop',
            'iyziEventType' => 'HPP',
            'paymentConversationId' => 'conv_nodrop',
            'status' => 'FAILURE',
        ]);

        // Set a new parameter via the notification setter
        $this->request->setNotificationData([
            'paymentId' => 'pay_nodrop',
            'iyziEventType' => 'HPP',
            'paymentConversationId' => 'conv_nodrop',
            'status' => 'FAILURE',
            'extraKey' => 'extraValue',
        ]);

        $data = $this->request->getData();

        $this->assertSame('pay_nodrop', $data['paymentId']);
        $this->assertSame('FAILURE', $data['status']);
        $this->assertSame('extraValue', $data['extraKey']);
    }
}
