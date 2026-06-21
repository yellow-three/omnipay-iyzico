<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Common\Message\RequestInterface;
use Omnipay\Iyzico\Message\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testIsSuccessful(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $this->assertTrue($response->isSuccessful());
    }

    public function testIsSuccessfulWithNonSuccessStatus(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'failure']
        );

        $this->assertFalse($response->isSuccessful());
    }

    public function testIsPending(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'pending']
        );

        $this->assertTrue($response->isPending());
    }

    public function testIsPendingWithNonPendingStatus(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $this->assertFalse($response->isPending());
    }

    public function testGetMessageWithError(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            [
                'errorMessage' => 'Payment failed',
                'errorCode' => '5001',
            ]
        );

        $this->assertSame('Payment failed (errorCode: 5001)', $response->getMessage());
    }

    public function testGetMessageWithoutError(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['message' => 'Transaction approved']
        );

        $this->assertSame('Transaction approved', $response->getMessage());
    }

    public function testGetMessageReturnsNullWhenNoMessage(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $this->assertNull($response->getMessage());
    }

    public function testGetTransactionReferenceWithPaymentId(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            [
                'paymentId' => 'pay_123',
                'conversationId' => 'conv_123',
            ]
        );

        $this->assertSame('pay_123', $response->getTransactionReference());
    }

    public function testGetTransactionReferenceFallsBackToConversationId(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['conversationId' => 'conv_123']
        );

        $this->assertSame('conv_123', $response->getTransactionReference());
    }

    public function testGetTransactionReferenceReturnsNullWhenNeitherPresent(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $this->assertNull($response->getTransactionReference());
    }

    public function testGetCheckoutFormContent(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['checkoutFormContent' => '<div>checkout form</div>']
        );

        $this->assertSame('<div>checkout form</div>', $response->getCheckoutFormContent());
    }

    public function testGetCheckoutFormContentReturnsNullWhenNotPresent(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $this->assertNull($response->getCheckoutFormContent());
    }

    public function testNormalizeDataWithObject(): void
    {
        $data = new class {
            public function getStatus(): string { return 'success'; }
            public function getPaymentId(): string { return 'pay_123'; }
            public function getConversationId(): string { return 'conv_123'; }
            public function getErrorCode(): string { return ''; }
            public function getErrorMessage(): string { return ''; }
            public function getErrorGroup(): string { return ''; }
            public function getLocale(): string { return 'TR'; }
            public function getSystemTime(): string { return '1234567890'; }
            public function getPaymentStatus(): string { return ''; }
            public function getPrice(): string { return ''; }
            public function getPaidPrice(): string { return ''; }
            public function getCurrency(): string { return 'TRY'; }
            public function getInstallment(): string { return '1'; }
            public function getFraudStatus(): string { return ''; }
            public function getBasketId(): string { return ''; }
            public function getCardType(): string { return ''; }
            public function getCardAssociation(): string { return ''; }
            public function getCardFamily(): string { return ''; }
            public function getCardToken(): string { return ''; }
            public function getCardUserKey(): string { return ''; }
            public function getBinNumber(): string { return ''; }
            public function getLastFourDigits(): string { return ''; }
            public function getAuthCode(): string { return ''; }
            public function getConnectorName(): string { return ''; }
            public function getPaymentTransactionId(): string { return ''; }
            public function getToken(): string { return ''; }
            public function getTokenExpireTime(): string { return ''; }
            public function getPaymentPageUrl(): string { return ''; }
            public function getCheckoutFormContent(): string { return ''; }
            public function getHtmlContent(): string { return ''; }
            public function getMdStatus(): string { return ''; }
            public function getCallbackUrl(): string { return ''; }
            public function getSignature(): string { return ''; }
        };

        $response = new Response($this->createMock(RequestInterface::class), $data);

        $this->assertSame('success', $response->getStatus());
        $this->assertSame('pay_123', $response->getPaymentId());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testNormalizeDataWithArray(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'success', 'paymentId' => 'pay_123']
        );

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('pay_123', $response->getPaymentId());
    }

    public function testNormalizeDataWithString(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            '{"status":"success","paymentId":"pay_123"}'
        );

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('pay_123', $response->getPaymentId());
    }

    public function testNormalizeDataWithInvalidString(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            'not-json'
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->getStatus());
    }

    public function testNormalizeDataWithNull(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            null
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->getStatus());
    }
}
