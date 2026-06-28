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

    public function testNormalizeTrailingZeroWithStandardPrice(): void
    {
        $this->assertSame('50', Response::normalizeTrailingZero('50.00'));
    }

    public function testNormalizeTrailingZeroWithSingleDecimal(): void
    {
        $this->assertSame('10.5', Response::normalizeTrailingZero('10.50'));
    }

    public function testNormalizeTrailingZeroWithMultipleDecimals(): void
    {
        $this->assertSame('10.51', Response::normalizeTrailingZero('10.510'));
    }

    public function testNormalizeTrailingZeroWithIntegerString(): void
    {
        $this->assertSame('100', Response::normalizeTrailingZero('100'));
    }

    public function testNormalizeTrailingZeroWithNonNumeric(): void
    {
        $this->assertSame('abc', Response::normalizeTrailingZero('abc'));
    }

    public function testNormalizeTrailingZeroWithZeroValue(): void
    {
        $this->assertSame('0', Response::normalizeTrailingZero('0.00'));
    }

    public function testVerifySignatureReturnsTrueWithValidSignature(): void
    {
        $data = [
            'paymentId' => 'pay_123',
            'currency' => 'TRY',
            'conversationId' => 'conv_123',
            'signature' => '80a72cfe1a88ca9e393a51b0b66aa3b2379b8f3767c1f0e667c777f6928f4295',
        ];
        $response = new Response($this->createMock(RequestInterface::class), $data);

        $this->assertTrue(
            $response->verifySignature('test-key', ['paymentId', 'currency', 'conversationId'])
        );
    }

    public function testVerifySignatureReturnsFalseWithTamperedData(): void
    {
        $data = [
            'paymentId' => 'pay_123',
            'currency' => 'TRY',
            'conversationId' => 'conv_999', // tampered
            'signature' => '80a72cfe1a88ca9e393a51b0b66aa3b2379b8f3767c1f0e667c777f6928f4295',
        ];
        $response = new Response($this->createMock(RequestInterface::class), $data);

        $this->assertFalse(
            $response->verifySignature('test-key', ['paymentId', 'currency', 'conversationId'])
        );
    }

    public function testVerifySignatureReturnsFalseWhenSignatureMissing(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['paymentId' => 'pay_123']
        );

        $this->assertFalse(
            $response->verifySignature('test-key', ['paymentId'])
        );
    }

    public function testVerifySignatureWithPriceNormalization(): void
    {
        // Compute expected: secretKey="sekret", paidPrice="100.50", price="100.50"
        // Message: "sekret:pay_1:TRY:basket_1:conv_1:100.5:100.5"
        $expected = hash_hmac('sha256', 'sekret:pay_1:TRY:basket_1:conv_1:100.5:100.5', 'sekret');

        $data = [
            'paymentId' => 'pay_1',
            'currency' => 'TRY',
            'basketId' => 'basket_1',
            'conversationId' => 'conv_1',
            'paidPrice' => '100.50',
            'price' => '100.50',
            'signature' => $expected,
        ];
        $response = new Response($this->createMock(RequestInterface::class), $data);

        $this->assertTrue(
            $response->verifySignature('sekret', ['paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price'])
        );
    }

    public function testIsSignatureValidReturnsNullWhenNotSet(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $this->assertNull($response->isSignatureValid());
    }

    public function testIsSignatureValidReturnsTrueWhenSet(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $response->setSignatureValid(true);
        $this->assertTrue($response->isSignatureValid());
    }

    public function testIsSignatureValidReturnsFalseWhenSet(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $response->setSignatureValid(false);
        $this->assertFalse($response->isSignatureValid());
    }

    public function testApplySignatureValidatesCorrectly(): void
    {
        $signature = hash_hmac('sha256', 'test-key:pay_1:conv_1', 'test-key');
        $data = [
            'paymentId' => 'pay_1',
            'conversationId' => 'conv_1',
            'signature' => $signature,
        ];
        $response = new Response($this->createMock(RequestInterface::class), $data);
        $response->applySignature('test-key', '3ds-init');

        $this->assertTrue($response->isSignatureValid());
    }

    public function testApplySignatureReturnsNullForUnknownEndpoint(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $response->applySignature('test-key', 'unknown-endpoint');
        $this->assertNull($response->isSignatureValid());
    }

    public function testGetSignatureFieldOrderReturnsExpectedFields(): void
    {
        $this->assertSame(
            ['paymentId', 'currency', 'basketId', 'conversationId', 'paidPrice', 'price'],
            Response::getSignatureFieldOrder('non-3ds')
        );
        $this->assertSame(
            ['paymentId', 'conversationId'],
            Response::getSignatureFieldOrder('3ds-init')
        );
        $this->assertSame(
            ['conversationId', 'token'],
            Response::getSignatureFieldOrder('checkout-init')
        );
        $this->assertNull(Response::getSignatureFieldOrder('nonexistent'));
    }

    public function testNormalizeDataWithRawResultIncludesNonWhitelistedFields(): void
    {
        $rawJson = json_encode([
            'status' => 'success',
            'paymentId' => 'pay_123',
            'conversationId' => 'conv_123',
            'subMerchantKey' => 'sub_merchant_abc',
            'referenceCode' => 'ref_456',
            'url' => 'https://example.com/link',
            'redirectUrl' => 'https://example.com/redirect',
        ]);

        $mock = new class($rawJson) {
            private string $rawResult;
            public function __construct(string $rawResult) { $this->rawResult = $rawResult; }
            public function getRawResult(): string { return $this->rawResult; }
        };

        $response = new Response(
            $this->createMock(RequestInterface::class),
            $mock
        );

        $data = $response->getData();

        $this->assertSame('success', $data['status']);
        $this->assertSame('pay_123', $data['paymentId']);
        $this->assertSame('sub_merchant_abc', $data['subMerchantKey']);
        $this->assertSame('ref_456', $data['referenceCode']);
        $this->assertSame('https://example.com/link', $data['url']);
        $this->assertSame('https://example.com/redirect', $data['redirectUrl']);
    }

    // ─── getSignatureFieldOrder endpoint tests ────────────────────────────

    public function testGetSignatureFieldOrderCancelReturnsExpectedFields(): void
    {
        $this->assertSame(
            ['paymentId', 'conversationId', 'status'],
            Response::getSignatureFieldOrder('cancel')
        );
    }

    public function testGetSignatureFieldOrderSettlementToBalanceReturnsExpectedFields(): void
    {
        $this->assertSame(
            ['paymentId', 'conversationId', 'status'],
            Response::getSignatureFieldOrder('settlement-to-balance')
        );
    }

    public function testGetSignatureFieldOrderApmRetrieveReturnsExpectedFields(): void
    {
        $this->assertSame(
            ['paymentId', 'currency', 'conversationId', 'price'],
            Response::getSignatureFieldOrder('apm-retrieve')
        );
    }

    public function testGetSignatureFieldOrderRefundToBalanceReturnsExpectedFields(): void
    {
        $this->assertSame(
            ['paymentId', 'status'],
            Response::getSignatureFieldOrder('refund-to-balance')
        );
    }

    public function testGetSignatureFieldOrderCreateCardReturnsExpectedFields(): void
    {
        $this->assertSame(
            ['cardUserKey', 'cardToken', 'status'],
            Response::getSignatureFieldOrder('create-card')
        );
    }

    public function testGetSignatureFieldOrderDeleteCardReturnsExpectedFields(): void
    {
        $this->assertSame(
            ['cardUserKey', 'cardToken', 'status'],
            Response::getSignatureFieldOrder('delete-card')
        );
    }

    public function testGetSignatureFieldOrderListCardsReturnsExpectedFields(): void
    {
        $this->assertSame(
            ['cardUserKey', 'status'],
            Response::getSignatureFieldOrder('list-cards')
        );
    }

    public function testGetSignatureFieldOrderBinNumberReturnsExpectedFields(): void
    {
        $this->assertSame(
            ['binNumber', 'status'],
            Response::getSignatureFieldOrder('bin-number')
        );
    }

    public function testGetSignatureFieldOrderInstallmentReturnsExpectedFields(): void
    {
        $this->assertSame(
            ['binNumber', 'status'],
            Response::getSignatureFieldOrder('installment')
        );
    }

    public function testGetSignatureFieldOrderLoyaltyReturnsExpectedFields(): void
    {
        $this->assertSame(
            ['cardNumber', 'status'],
            Response::getSignatureFieldOrder('loyalty')
        );
    }

    // ─── IYZICO_FIELDS constant tests ─────────────────────────────────────

    public function testIyzicoFieldsIncludesNewEntries(): void
    {
        $reflection = new \ReflectionClass(Response::class);
        $constants = $reflection->getReflectionConstants();

        foreach ($constants as $constant) {
            if ($constant->getName() === 'IYZICO_FIELDS') {
                $fields = $constant->getValue();
                $this->assertContains('rewardAmount', $fields);
                $this->assertContains('rewardUsage', $fields);
                $this->assertContains('commissionRate', $fields);
                $this->assertContains('commissionRateAmount', $fields);
                $this->assertContains('phase', $fields);
                $this->assertContains('posOrderId', $fields);
                return;
            }
        }

        $this->fail('IYZICO_FIELDS constant not found');
    }

    // ─── isSignatureValid additional tests ─────────────────────────────────

    public function testApplySignatureWithMismatchedSignatureReturnsFalse(): void
    {
        $data = [
            'paymentId' => 'pay_1',
            'conversationId' => 'conv_1',
            'signature' => 'wrong_signature_value_that_does_not_match',
        ];
        $response = new Response($this->createMock(RequestInterface::class), $data);
        $response->applySignature('test-key', '3ds-init');

        $this->assertFalse($response->isSignatureValid());
    }
}
