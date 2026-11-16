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
            'signature' => hash_hmac('sha256', 'pay_123:TRY:conv_123', 'test-key'),
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
        // paidPrice="100.50", price="100.50" are normalized to "100.5"; the secret key
        // is only the HMAC key and must not be part of the signed message.
        $expected = hash_hmac('sha256', 'pay_1:TRY:basket_1:conv_1:100.5:100.5', 'sekret');

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

    /**
     * Official vector from iyzipay-php samples/callbackUrl_Signature_Validation.php.
     * Guards against re-introducing the secret key into the signed message.
     */
    public function testVerifySignatureMatchesOfficialIyzicoSampleVector(): void
    {
        $response = new Response($this->createMock(RequestInterface::class), [
            'conversationData' => '',
            'conversationId' => '',
            'mdStatus' => '1',
            'paymentId' => '22484292',
            'status' => 'success',
            'signature' => 'a4f73b80bb32a6ef8358090bbd8609a49a7b53f463048f8ef147496e236d04f0',
        ]);

        $this->assertTrue($response->verifySignature(
            'jLc7GQHD2pyJoqXDeEcTnGHYtP7Ai5jl',
            Response::getSignatureFieldOrder('callback-redirect')
        ));
    }

    public function testVerifySignatureRejectsSecretKeyPrefixedMessage(): void
    {
        $response = new Response($this->createMock(RequestInterface::class), [
            'paymentId' => 'pay_1',
            'conversationId' => 'conv_1',
            'signature' => hash_hmac('sha256', 'test-key:pay_1:conv_1', 'test-key'),
        ]);

        $this->assertFalse($response->verifySignature('test-key', ['paymentId', 'conversationId']));
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
        $signature = hash_hmac('sha256', 'pay_1:conv_1', 'test-key');
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

    /**
     * Orders mirror the calculateHmacSHA256Signature() calls in iyzipay-php samples.
     */
    public function testGetSignatureFieldOrderMatchesSdkSamplesForBkmAndApm(): void
    {
        $this->assertSame(['token', 'conversationId'], Response::getSignatureFieldOrder('bkm-init'));
        $this->assertSame(
            ['paymentId', 'paymentStatus', 'basketId', 'conversationId', 'currency', 'paidPrice', 'price', 'token'],
            Response::getSignatureFieldOrder('bkm-retrieve')
        );
        $this->assertSame(['paymentId', 'redirectUrl'], Response::getSignatureFieldOrder('apm-init'));
        $this->assertSame(
            ['currency', 'basketId', 'conversationId', 'paidPrice', 'price', 'paymentId', 'redirectUrl'],
            Response::getSignatureFieldOrder('apm-retrieve')
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unsignedEndpoints')]
    public function testUnsignedEndpointsAreReportedAsNotVerified(string $endpoint): void
    {
        $this->assertNull(Response::getSignatureFieldOrder($endpoint));

        // iyzico sends no signature here, so "not verified" (null) — not "invalid" (false).
        $response = new Response($this->createMock(RequestInterface::class), ['status' => 'success']);
        $response->applySignature('test-key', $endpoint);

        $this->assertNull($response->isSignatureValid());
    }

    public static function unsignedEndpoints(): array
    {
        return [
            ['cancel'], ['settlement-to-balance'], ['refund-to-balance'], ['basic-bkm-init'],
            ['create-card'], ['delete-card'], ['list-cards'],
            ['bin-number'], ['installment'], ['loyalty'],
            ['marketplace-approve-payment'], ['reporting-payment-detail'], ['iyzilink-create'],
        ];
    }

    public function testApplySignatureValidatesApmInitWithRedirectUrl(): void
    {
        $data = [
            'paymentId' => 'pay_1',
            'redirectUrl' => 'https://bank.example/pay',
            'signature' => hash_hmac('sha256', 'pay_1:https://bank.example/pay', 'test-key'),
        ];
        $response = new Response($this->createMock(RequestInterface::class), $data);
        $response->applySignature('test-key', 'apm-init');

        $this->assertTrue($response->isSignatureValid());
    }

    // ─── stored card / item transaction accessors ─────────────────────────

    public function testGetCardTokenAndCardUserKey(): void
    {
        $response = new Response(
            $this->createMock(RequestInterface::class),
            ['status' => 'success', 'cardToken' => 'tok_1', 'cardUserKey' => 'key_1']
        );

        $this->assertSame('tok_1', $response->getCardToken());
        $this->assertSame('key_1', $response->getCardUserKey());
    }

    public function testGetCardTokenAndCardUserKeyDefaultToNull(): void
    {
        $response = new Response($this->createMock(RequestInterface::class), ['status' => 'success']);

        $this->assertNull($response->getCardToken());
        $this->assertNull($response->getCardUserKey());
    }

    public function testGetPaymentTransactionIdsReadsItemTransactions(): void
    {
        $response = new Response($this->createMock(RequestInterface::class), [
            'status' => 'success',
            'itemTransactions' => [
                ['itemId' => 'A', 'paymentTransactionId' => '111'],
                ['itemId' => 'B', 'paymentTransactionId' => 222],
                ['itemId' => 'C'],
            ],
        ]);

        $this->assertCount(3, $response->getItemTransactions());
        $this->assertSame(['111', '222'], $response->getPaymentTransactionIds());
    }

    public function testGetPaymentTransactionIdsIsEmptyWithoutItemTransactions(): void
    {
        $response = new Response($this->createMock(RequestInterface::class), ['status' => 'success']);

        $this->assertSame([], $response->getItemTransactions());
        $this->assertSame([], $response->getPaymentTransactionIds());
    }

    // ─── documented field getters ─────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\DataProvider('stringFieldGetters')]
    public function testStringFieldGetters(string $getter, string $field): void
    {
        $present = new Response($this->createMock(RequestInterface::class), [$field => 'value']);
        $numeric = new Response($this->createMock(RequestInterface::class), [$field => 454359]);
        $absent = new Response($this->createMock(RequestInterface::class), ['status' => 'success']);

        $this->assertSame('value', $present->{$getter}());
        $this->assertSame('454359', $numeric->{$getter}());
        $this->assertNull($absent->{$getter}());
    }

    public static function stringFieldGetters(): array
    {
        $fields = [
            'cardType', 'cardAssociation', 'cardFamily', 'binNumber', 'lastFourDigits', 'authCode',
            'connectorName', 'bankName', 'bankCode', 'externalId', 'cardAlias', 'cardBankCode',
            'cardBankName', 'signature', 'mdStatus', 'callbackUrl', 'paymentPageUrl',
            'payWithIyzicoPageUrl', 'payWithIyzicoContent',
        ];

        return array_map(static fn (string $f) => ['get' . ucfirst($f), $f], $fields);
    }

    public function testGetCommercialAndInstallmentDetails(): void
    {
        $response = new Response($this->createMock(RequestInterface::class), [
            'commercial' => '1',
            'installmentDetails' => [['binNumber' => '454359']],
        ]);
        $empty = new Response($this->createMock(RequestInterface::class), ['status' => 'success']);

        $this->assertSame(1, $response->getCommercial());
        $this->assertSame([['binNumber' => '454359']], $response->getInstallmentDetails());
        $this->assertNull($empty->getCommercial());
        $this->assertNull($empty->getInstallmentDetails());
    }

    public function testGetPaymentTransactionIdFallsBackToFirstBasketItem(): void
    {
        $response = new Response($this->createMock(RequestInterface::class), [
            'itemTransactions' => [['paymentTransactionId' => 111], ['paymentTransactionId' => 222]],
        ]);
        $empty = new Response($this->createMock(RequestInterface::class), ['status' => 'success']);

        $this->assertSame('111', $response->getPaymentTransactionId());
        $this->assertSame('111', $response->getTransactionId());
        $this->assertNull($empty->getPaymentTransactionId());
    }

    /**
     * Every method listed in the README "Response Methods" table must exist.
     */
    public function testReadmeResponseMethodsExist(): void
    {
        $readme = file_get_contents(__DIR__ . '/../../README.md');
        preg_match_all('/^\| `([a-zA-Z]+)\(\)` \|/m', $readme, $matches);

        $this->assertNotEmpty($matches[1]);

        foreach (array_unique($matches[1]) as $method) {
            $this->assertTrue(
                method_exists(\Omnipay\Iyzico\Message\RedirectResponse::class, $method),
                "README documents {$method}() but it does not exist"
            );
        }
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
