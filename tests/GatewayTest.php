<?php

namespace Omnipay\Iyzico\Tests;

use Omnipay\Iyzico\Gateway;
use PHPUnit\Framework\TestCase;

class GatewayTest extends TestCase
{
    private Gateway $gateway;

    protected function setUp(): void
    {
        $this->gateway = new Gateway();
    }

    public function testGetName(): void
    {
        $this->assertSame('Iyzico', $this->gateway->getName());
    }

    public function testDefaultParameters(): void
    {
        $defaults = $this->gateway->getDefaultParameters();

        $this->assertArrayHasKey('apiKey', $defaults);
        $this->assertArrayHasKey('secretKey', $defaults);
        $this->assertArrayHasKey('baseUrl', $defaults);
        $this->assertArrayHasKey('testMode', $defaults);
        $this->assertArrayHasKey('locale', $defaults);
        $this->assertArrayHasKey('currency', $defaults);
        $this->assertArrayHasKey('secure3d', $defaults);
    }

    public function testSetAndGetApiKey(): void
    {
        $this->gateway->setApiKey('test-api-key');
        $this->assertSame('test-api-key', $this->gateway->getApiKey());
    }

    public function testSetAndGetSecretKey(): void
    {
        $this->gateway->setSecretKey('test-secret-key');
        $this->assertSame('test-secret-key', $this->gateway->getSecretKey());
    }

    public function testSetAndGetBaseUrl(): void
    {
        $this->gateway->setBaseUrl('https://api.iyzipay.com');
        $this->assertSame('https://api.iyzipay.com', $this->gateway->getBaseUrl());
    }

    public function testSetAndGetLocale(): void
    {
        $this->gateway->setLocale('EN');
        $this->assertSame('EN', $this->gateway->getLocale());
    }

    public function testSetAndGetSecure3d(): void
    {
        $this->gateway->setSecure3d(false);
        $this->assertFalse($this->gateway->getSecure3d());

        $this->gateway->setSecure3d(true);
        $this->assertTrue($this->gateway->getSecure3d());
    }

    public function testSetAndGetInstallment(): void
    {
        $this->gateway->setInstallment(3);
        $this->assertSame(3, $this->gateway->getInstallment());
    }

    public function testSetAndGetIdentityNumber(): void
    {
        $this->gateway->setIdentityNumber('12345678901');
        $this->assertSame('12345678901', $this->gateway->getIdentityNumber());
    }

    public function testPurchaseRequestCreation(): void
    {
        $request = $this->gateway->purchase([
            'amount' => '100.00',
            'card' => [
                'number' => '4111111111111111',
                'expiryMonth' => '12',
                'expiryYear' => '2030',
                'cvv' => '123',
                'firstName' => 'Test',
                'lastName' => 'User',
            ],
        ]);

        $this->assertInstanceOf(\Omnipay\Iyzico\Message\PurchaseRequest::class, $request);
    }

    public function testRefundRequestCreation(): void
    {
        $request = $this->gateway->refund([
            'amount' => '50.00',
            'paymentTransactionId' => 'tx_123',
            'conversationId' => 'conv_123',
        ]);

        $this->assertInstanceOf(\Omnipay\Iyzico\Message\RefundRequest::class, $request);
    }

    public function testVoidRequestCreation(): void
    {
        $request = $this->gateway->void([
            'paymentId' => 'pay_123',
            'conversationId' => 'conv_123',
        ]);

        $this->assertInstanceOf(\Omnipay\Iyzico\Message\VoidRequest::class, $request);
    }

    public function testCaptureRequestCreation(): void
    {
        $request = $this->gateway->capture([
            'paymentId' => 'pay_123',
            'conversationId' => 'conv_123',
        ]);

        $this->assertInstanceOf(\Omnipay\Iyzico\Message\CaptureRequest::class, $request);
    }

    public function testFetchTransactionRequestCreation(): void
    {
        $request = $this->gateway->fetchTransaction([
            'paymentId' => 'pay_123',
            'conversationId' => 'conv_123',
        ]);

        $this->assertInstanceOf(\Omnipay\Iyzico\Message\FetchTransactionRequest::class, $request);
    }
}
