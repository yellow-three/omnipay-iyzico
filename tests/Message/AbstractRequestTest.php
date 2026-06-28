<?php

namespace Omnipay\Iyzico\Tests\Message;

use Iyzipay\Model\Currency as IyzicoCurrency;
use Iyzipay\Model\Locale as IyzicoLocale;
use Iyzipay\Model\PaymentChannel as IyzicoPaymentChannel;
use Iyzipay\Model\PaymentGroup as IyzicoPaymentGroup;
use Iyzipay\Options as IyzicoOptions;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Item;
use Omnipay\Iyzico\Message\AbstractRequest;
use PHPUnit\Framework\TestCase;

/**
 * Test double that exposes protected methods of AbstractRequest for testing.
 */
class TestAbstractRequest extends AbstractRequest
{
    public function __construct()
    {
        // Skip the parent constructor which requires HTTP client/request.
        // We call initialize() directly to set up the parameter bag.
        $this->initialize();
    }

    public function getData(): array
    {
        return [];
    }

    public function sendData($data)
    {
        return $data;
    }

    public function publicGenerateConversationId(): string
    {
        return $this->generateConversationId();
    }

    public function publicCreateIyzicoOptions(): IyzicoOptions
    {
        return $this->createIyzicoOptions();
    }

    public function publicMapLocale(string $locale): string
    {
        return $this->mapLocale($locale);
    }

    public function publicMapCurrency(string $currency): string
    {
        return $this->mapCurrency($currency);
    }

    public function publicMapPaymentChannel(string $channel): string
    {
        return $this->mapPaymentChannel($channel);
    }

    public function publicMapPaymentGroup(string $group): string
    {
        return $this->mapPaymentGroup($group);
    }

    public function publicBuildBuyer(CreditCard $card): \Iyzipay\Model\Buyer
    {
        return $this->buildBuyer($card);
    }

    public function publicBuildShippingAddress(CreditCard $card): \Iyzipay\Model\Address
    {
        return $this->buildShippingAddress($card);
    }

    public function publicBuildBillingAddress(CreditCard $card): \Iyzipay\Model\Address
    {
        return $this->buildBillingAddress($card);
    }

    public function publicBuildPaymentCard(CreditCard $card, bool $registerCard = false): \Iyzipay\Model\PaymentCard
    {
        return $this->buildPaymentCard($card, $registerCard);
    }

    public function publicBuildBasketItems(): array
    {
        return $this->buildBasketItems();
    }
}

class AbstractRequestTest extends TestCase
{
    private TestAbstractRequest $request;

    protected function setUp(): void
    {
        $this->request = new TestAbstractRequest();
    }

    // ─── Getter / Setter Tests ───────────────────────────────────────────────

    public function testGetAndSetApiKey(): void
    {
        $this->request->setApiKey('test-api-key');
        $this->assertSame('test-api-key', $this->request->getApiKey());
    }

    public function testGetAndSetSecretKey(): void
    {
        $this->request->setSecretKey('test-secret-key');
        $this->assertSame('test-secret-key', $this->request->getSecretKey());
    }

    public function testGetAndSetPaymentId(): void
    {
        $this->request->setPaymentId('pay_12345');
        $this->assertSame('pay_12345', $this->request->getPaymentId());
    }

    public function testConversationIdReturnsSetValue(): void
    {
        $this->request->setConversationId('conv_abc');
        $this->assertSame('conv_abc', $this->request->getConversationId());
    }

    public function testConversationIdAutoGeneratesWhenEmpty(): void
    {
        $conversationId = $this->request->getConversationId();

        $this->assertNotEmpty($conversationId);
        $this->assertStringStartsWith('txn_', $conversationId);
    }

    public function testConversationIdGeneratesUniqueValuesOnEachCall(): void
    {
        $id1 = $this->request->getConversationId();
        $id2 = $this->request->getConversationId();

        $this->assertNotSame($id1, $id2);
    }

    public function testGenerateConversationId(): void
    {
        $id = $this->request->publicGenerateConversationId();

        $this->assertNotEmpty($id);
        $this->assertStringStartsWith('txn_', $id);
    }

    // ─── mapLocale Tests ────────────────────────────────────────────────────

    /** @dataProvider localeProvider */
    public function testMapLocale(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->request->publicMapLocale($input));
    }

    public static function localeProvider(): array
    {
        return [
            'TR uppercase maps to IyzicoLocale::TR'    => ['TR', IyzicoLocale::TR],
            'tr lowercase maps to IyzicoLocale::TR'    => ['tr', IyzicoLocale::TR],
            'EN uppercase maps to IyzicoLocale::EN'    => ['EN', IyzicoLocale::EN],
            'en lowercase maps to IyzicoLocale::EN'    => ['en', IyzicoLocale::EN],
            'unknown locale defaults to TR'            => ['FR', IyzicoLocale::TR],
            'empty string defaults to TR'              => ['', IyzicoLocale::TR],
        ];
    }

    // ─── mapCurrency Tests ──────────────────────────────────────────────────

    /** @dataProvider currencyProvider */
    public function testMapCurrency(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->request->publicMapCurrency($input));
    }

    public static function currencyProvider(): array
    {
        return [
            'TRY maps to TL'       => ['TRY', IyzicoCurrency::TL],
            'TL maps to TL'        => ['TL', IyzicoCurrency::TL],
            'try lowercase'        => ['try', IyzicoCurrency::TL],
            'tl lowercase'         => ['tl', IyzicoCurrency::TL],
            'EUR'                  => ['EUR', IyzicoCurrency::EUR],
            'USD'                  => ['USD', IyzicoCurrency::USD],
            'GBP'                  => ['GBP', IyzicoCurrency::GBP],
            'RUB'                  => ['RUB', IyzicoCurrency::RUB],
            'IRR'                  => ['IRR', IyzicoCurrency::IRR],
            'NOK'                  => ['NOK', IyzicoCurrency::NOK],
            'CHF'                  => ['CHF', IyzicoCurrency::CHF],
        ];
    }

    public function testMapCurrencyThrowsOnUnknownCurrency(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Unsupported currency:');
        $this->request->publicMapCurrency('XYZ');
    }

    public function testMapCurrencyThrowsOnEmptyCurrency(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Unsupported currency:');
        $this->request->publicMapCurrency('');
    }

    // ─── mapPaymentChannel Tests ────────────────────────────────────────────

    /** @dataProvider paymentChannelProvider */
    public function testMapPaymentChannel(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->request->publicMapPaymentChannel($input));
    }

    public static function paymentChannelProvider(): array
    {
        return [
            'WEB'                          => ['WEB', IyzicoPaymentChannel::WEB],
            'web lowercase'                => ['web', IyzicoPaymentChannel::WEB],
            'MOBILE'                       => ['MOBILE', IyzicoPaymentChannel::MOBILE],
            'mobile lowercase'             => ['mobile', IyzicoPaymentChannel::MOBILE],
            'MOBILE_WEB'                   => ['MOBILE_WEB', IyzicoPaymentChannel::MOBILE_WEB],
            'mobile_web lowercase'         => ['mobile_web', IyzicoPaymentChannel::MOBILE_WEB],
            'unknown falls back to WEB'    => ['POS', IyzicoPaymentChannel::WEB],
        ];
    }

    // ─── mapPaymentGroup Tests ──────────────────────────────────────────────

    /** @dataProvider paymentGroupProvider */
    public function testMapPaymentGroup(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->request->publicMapPaymentGroup($input));
    }

    public static function paymentGroupProvider(): array
    {
        return [
            'default PRODUCT'                => ['PRODUCT', IyzicoPaymentGroup::PRODUCT],
            'product lowercase'              => ['product', IyzicoPaymentGroup::PRODUCT],
            'LISTING'                        => ['LISTING', IyzicoPaymentGroup::LISTING],
            'listing lowercase'              => ['listing', IyzicoPaymentGroup::LISTING],
            'SUBSCRIPTION'                   => ['SUBSCRIPTION', IyzicoPaymentGroup::SUBSCRIPTION],
            'subscription lowercase'         => ['subscription', IyzicoPaymentGroup::SUBSCRIPTION],
            'unknown falls back to PRODUCT'  => ['OTHER', IyzicoPaymentGroup::PRODUCT],
        ];
    }

    // ─── Builder Tests ──────────────────────────────────────────────────────

    private function createTestCreditCard(): CreditCard
    {
        return new CreditCard([
            'firstName'          => 'John',
            'lastName'           => 'Doe',
            'number'             => '4111111111111111',
            'expiryMonth'        => '12',
            'expiryYear'         => '2030',
            'cvv'                => '123',
            'email'              => 'john@example.com',
            'phone'              => '+905551112233',
            'billingAddress1'    => 'Billing St. No:1',
            'billingCity'        => 'Istanbul',
            'billingCountry'     => 'Turkey',
            'billingPostcode'    => '34000',
            'shippingFirstName'  => 'Jane',
            'shippingLastName'   => 'Doe',
            'shippingCity'       => 'Ankara',
            'shippingCountry'    => 'Turkey',
            'shippingAddress1'   => 'Shipping St. No:2',
            'shippingPostcode'   => '06000',
        ]);
    }

    public function testBuildBuyer(): void
    {
        $card = $this->createTestCreditCard();
        $this->request->setConversationId('conv_test');
        $this->request->setIdentityNumber('12345678901');
        $this->request->setClientIp('192.168.1.1');

        $buyer = $this->request->publicBuildBuyer($card);

        $this->assertInstanceOf(\Iyzipay\Model\Buyer::class, $buyer);
        $this->assertSame('conv_test', $buyer->getId());
        $this->assertSame('John', $buyer->getName());
        $this->assertSame('Doe', $buyer->getSurname());
        $this->assertSame('+905551112233', $buyer->getGsmNumber());
        $this->assertSame('john@example.com', $buyer->getEmail());
        $this->assertSame('12345678901', $buyer->getIdentityNumber());
        $this->assertSame('Billing St. No:1', $buyer->getRegistrationAddress());
        $this->assertSame('192.168.1.1', $buyer->getIp());
        $this->assertSame('Istanbul', $buyer->getCity());
        $this->assertSame('Turkey', $buyer->getCountry());
        $this->assertSame('34000', $buyer->getZipCode());
        $this->assertNotEmpty($buyer->getRegistrationDate());
        $this->assertNotEmpty($buyer->getLastLoginDate());
    }

    public function testBuildBuyerUsesAutoGeneratedConversationId(): void
    {
        $card = $this->createTestCreditCard();
        // Do NOT set a conversationId – let it auto-generate
        $this->request->setIdentityNumber('12345678901');
        $buyer = $this->request->publicBuildBuyer($card);

        $this->assertStringStartsWith('txn_', $buyer->getId());
    }

    public function testBuildShippingAddress(): void
    {
        $card = $this->createTestCreditCard();

        $address = $this->request->publicBuildShippingAddress($card);

        $this->assertInstanceOf(\Iyzipay\Model\Address::class, $address);
        $this->assertSame('Jane Doe', $address->getContactName());
        $this->assertSame('Ankara', $address->getCity());
        $this->assertSame('Turkey', $address->getCountry());
        $this->assertSame('Shipping St. No:2', $address->getAddress());
        $this->assertSame('06000', $address->getZipCode());
    }

    public function testBuildBillingAddress(): void
    {
        $card = $this->createTestCreditCard();

        $address = $this->request->publicBuildBillingAddress($card);

        $this->assertInstanceOf(\Iyzipay\Model\Address::class, $address);
        $this->assertSame('John Doe', $address->getContactName());
        $this->assertSame('Istanbul', $address->getCity());
        $this->assertSame('Turkey', $address->getCountry());
        $this->assertSame('Billing St. No:1', $address->getAddress());
        $this->assertSame('34000', $address->getZipCode());
    }

    public function testBuildPaymentCard(): void
    {
        $card = $this->createTestCreditCard();

        $paymentCard = $this->request->publicBuildPaymentCard($card);

        $this->assertInstanceOf(\Iyzipay\Model\PaymentCard::class, $paymentCard);
        $this->assertSame('John Doe', $paymentCard->getCardHolderName());
        $this->assertSame('4111111111111111', $paymentCard->getCardNumber());
        // CreditCard stores month/year as integers
        $this->assertSame(12, $paymentCard->getExpireMonth());
        $this->assertSame(2030, $paymentCard->getExpireYear());
        $this->assertSame('123', $paymentCard->getCvc());
        $this->assertSame(0, $paymentCard->getRegisterCard());
    }

    // ─── buildBasketItems Tests ─────────────────────────────────────────────

    public function testBuildBasketItemsWithItems(): void
    {
        $item1 = new Item(['name' => 'Product 1', 'description' => 'Description 1', 'price' => '10.00']);
        $item2 = new Item(['name' => 'Product 2', 'description' => 'Description 2', 'price' => '20.00']);
        $this->request->setItems([$item1, $item2]);

        $basketItems = $this->request->publicBuildBasketItems();

        $this->assertCount(2, $basketItems);

        $this->assertSame('Product 1', $basketItems[0]->getId());
        $this->assertSame('Product 1', $basketItems[0]->getName());
        $this->assertSame('Description 1', $basketItems[0]->getCategory1());
        $this->assertSame(\Iyzipay\Model\BasketItemType::PHYSICAL, $basketItems[0]->getItemType());
        $this->assertSame('10.00', $basketItems[0]->getPrice());

        $this->assertSame('Product 2', $basketItems[1]->getId());
        $this->assertSame('Product 2', $basketItems[1]->getName());
        $this->assertSame('Description 2', $basketItems[1]->getCategory1());
        $this->assertSame(\Iyzipay\Model\BasketItemType::PHYSICAL, $basketItems[1]->getItemType());
        $this->assertSame('20.00', $basketItems[1]->getPrice());
    }

    public function testBuildBasketItemsFallbackWhenNoItems(): void
    {
        $this->request->setAmount('100.00');
        $this->request->setDescription('Test Payment');

        $basketItems = $this->request->publicBuildBasketItems();

        $this->assertCount(1, $basketItems);
        $this->assertSame('ITEM001', $basketItems[0]->getId());
        $this->assertSame('Test Payment', $basketItems[0]->getName());
        $this->assertSame('Genel', $basketItems[0]->getCategory1());
        $this->assertSame(\Iyzipay\Model\BasketItemType::PHYSICAL, $basketItems[0]->getItemType());
        $this->assertSame('100.00', $basketItems[0]->getPrice());
    }

    public function testBuildBasketItemsFallbackWithDefaultName(): void
    {
        $this->request->setAmount('50.00');

        $basketItems = $this->request->publicBuildBasketItems();

        $this->assertCount(1, $basketItems);
        $this->assertSame('ITEM001', $basketItems[0]->getId());
        $this->assertSame('Payment', $basketItems[0]->getName());
        $this->assertSame('Genel', $basketItems[0]->getCategory1());
        $this->assertSame(\Iyzipay\Model\BasketItemType::PHYSICAL, $basketItems[0]->getItemType());
        $this->assertSame('50.00', $basketItems[0]->getPrice());
    }

    public function testBuildBasketItemCategory1FallsBackToGenelWhenDescriptionIsEmpty(): void
    {
        $item = new Item(['name' => 'Simple Item', 'description' => '', 'price' => '15.00']);
        $this->request->setItems([$item]);

        $basketItems = $this->request->publicBuildBasketItems();

        $this->assertCount(1, $basketItems);
        $this->assertSame('Genel', $basketItems[0]->getCategory1());
    }

    public function testBuildPaymentCardWithRegisterCardTrue(): void
    {
        $card = $this->createTestCreditCard();

        $paymentCard = $this->request->publicBuildPaymentCard($card, true);

        $this->assertInstanceOf(\Iyzipay\Model\PaymentCard::class, $paymentCard);
        $this->assertSame(1, $paymentCard->getRegisterCard());
    }

    public function testBuildPaymentCardWithoutRegisterCardDefaultsToZero(): void
    {
        $card = $this->createTestCreditCard();

        $paymentCard = $this->request->publicBuildPaymentCard($card);

        $this->assertInstanceOf(\Iyzipay\Model\PaymentCard::class, $paymentCard);
        $this->assertSame(0, $paymentCard->getRegisterCard());
    }

    public function testBuildBasketItemsWithThreeItemsReturnsCorrectIds(): void
    {
        $item1 = new Item(['name' => 'item-1', 'description' => 'Desc 1', 'price' => '10.00']);
        $item2 = new Item(['name' => 'item-2', 'description' => 'Desc 2', 'price' => '20.00']);
        $item3 = new Item(['name' => 'item-3', 'description' => 'Desc 3', 'price' => '30.00']);
        $this->request->setItems([$item1, $item2, $item3]);

        $basketItems = $this->request->publicBuildBasketItems();

        $this->assertCount(3, $basketItems);
        $this->assertSame('item-1', $basketItems[0]->getId());
        $this->assertSame('item-2', $basketItems[1]->getId());
        $this->assertSame('item-3', $basketItems[2]->getId());
    }

    public function testBuildBasketItemsSetsCategory2WithItemDescription(): void
    {
        $item = new Item(['name' => 'Widget', 'description' => 'A widget description', 'price' => '25.00']);
        $this->request->setItems([$item]);

        $basketItems = $this->request->publicBuildBasketItems();

        $this->assertCount(1, $basketItems);
        $this->assertSame('A widget description', $basketItems[0]->getCategory2());
    }

    public function testBuildBasketItemsRespectsItemType(): void
    {
        $item = $this->createItemWithType(\Iyzipay\Model\BasketItemType::VIRTUAL);
        $this->request->setItems([$item]);

        $basketItems = $this->request->publicBuildBasketItems();

        $this->assertCount(1, $basketItems);
        $this->assertSame(\Iyzipay\Model\BasketItemType::VIRTUAL, $basketItems[0]->getItemType());
    }

    public function testBuildBasketItemsDefaultsToPhysicalWhenNoType(): void
    {
        $item = new Item([
            'name' => 'Physical Item',
            'description' => 'Physical goods',
            'price' => '30.00',
        ]);
        $this->request->setItems([$item]);

        $basketItems = $this->request->publicBuildBasketItems();

        $this->assertCount(1, $basketItems);
        $this->assertSame(\Iyzipay\Model\BasketItemType::PHYSICAL, $basketItems[0]->getItemType());
    }

    // ─── createIyzicoOptions Tests ──────────────────────────────────────────

    public function testCreateIyzicoOptions(): void
    {
        $this->request->setApiKey('api-key-123');
        $this->request->setSecretKey('secret-key-456');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $options = $this->request->publicCreateIyzicoOptions();

        $this->assertInstanceOf(IyzicoOptions::class, $options);
        $this->assertSame('api-key-123', $options->getApiKey());
        $this->assertSame('secret-key-456', $options->getSecretKey());
        $this->assertSame('https://sandbox-api.iyzipay.com', $options->getBaseUrl());
    }

    public function testCreateIyzicoOptionsWithEmptyValues(): void
    {
        $this->request->setApiKey('');
        $this->request->setSecretKey('');
        $this->request->setBaseUrl('');

        $options = $this->request->publicCreateIyzicoOptions();

        $this->assertInstanceOf(IyzicoOptions::class, $options);
        $this->assertSame('', $options->getApiKey());
        $this->assertSame('', $options->getSecretKey());
        $this->assertSame('', $options->getBaseUrl());
    }

    private function createItemWithType(string $type): Item
    {
        return new class ($type) extends Item {
            private string $itemType;

            public function __construct(string $itemType, ?array $parameters = null)
            {
                $this->itemType = $itemType;
                parent::__construct($parameters);
            }

            public function getType(): string
            {
                return $this->itemType;
            }
        };
    }
}
