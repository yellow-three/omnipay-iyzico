<?php

namespace Omnipay\Iyzico\Message;

use Iyzipay\Model\Locale as IyzicoLocale;
use Iyzipay\Model\Currency as IyzicoCurrency;
use Iyzipay\Model\PaymentChannel as IyzicoPaymentChannel;
use Iyzipay\Model\PaymentGroup as IyzicoPaymentGroup;
use Iyzipay\Options as IyzicoOptions;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

abstract class AbstractRequest extends BaseAbstractRequest
{
    public function getApiKey(): string
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey(string $value): static
    {
        return $this->setParameter('apiKey', $value);
    }

    public function getSecretKey(): string
    {
        return $this->getParameter('secretKey');
    }

    public function setSecretKey(string $value): static
    {
        return $this->setParameter('secretKey', $value);
    }

    public function getBaseUrl(): string
    {
        return $this->getParameter('baseUrl');
    }

    public function setBaseUrl(string $value): static
    {
        return $this->setParameter('baseUrl', $value);
    }

    public function getLocale(): string
    {
        return $this->getParameter('locale');
    }

    public function setLocale(string $value): static
    {
        return $this->setParameter('locale', $value);
    }

    public function getConversationId(): string
    {
        return $this->getParameter('conversationId') ?: $this->generateConversationId();
    }

    public function setConversationId(string $value): static
    {
        return $this->setParameter('conversationId', $value);
    }

    public function getPaymentChannel(): string
    {
        return $this->getParameter('paymentChannel');
    }

    public function setPaymentChannel(string $value): static
    {
        return $this->setParameter('paymentChannel', $value);
    }

    public function getPaymentGroup(): string
    {
        return $this->getParameter('paymentGroup');
    }

    public function setPaymentGroup(string $value): static
    {
        return $this->setParameter('paymentGroup', $value);
    }

    public function getInstallment(): int
    {
        return (int) $this->getParameter('installment');
    }

    public function setInstallment(int $value): static
    {
        return $this->setParameter('installment', $value);
    }

    public function getIdentityNumber(): ?string
    {
        return $this->getParameter('identityNumber');
    }

    public function setIdentityNumber(string $value): static
    {
        return $this->setParameter('identityNumber', $value);
    }

    public function getSecure3d(): bool
    {
        return (bool) $this->getParameter('secure3d');
    }

    public function setSecure3d(bool $value): static
    {
        return $this->setParameter('secure3d', $value);
    }

    public function getPaymentId(): string
    {
        return $this->getParameter('paymentId');
    }

    public function setPaymentId(string $value): static
    {
        return $this->setParameter('paymentId', $value);
    }

    public function getBinNumber(): string
    {
        return $this->getParameter('binNumber');
    }

    public function setBinNumber(string $value): static
    {
        return $this->setParameter('binNumber', $value);
    }

    public function getCardUserKey(): string
    {
        return $this->getParameter('cardUserKey');
    }

    public function setCardUserKey(string $value): static
    {
        return $this->setParameter('cardUserKey', $value);
    }

    public function getCardToken(): string
    {
        return $this->getParameter('cardToken');
    }

    public function setCardToken(string $value): static
    {
        return $this->setParameter('cardToken', $value);
    }

    public function getEmail(): string
    {
        return $this->getParameter('email');
    }

    public function setEmail(string $value): static
    {
        return $this->setParameter('email', $value);
    }

    protected function generateConversationId(): string
    {
        return uniqid('txn_', true);
    }

    protected function createIyzicoOptions(): IyzicoOptions
    {
        $options = new IyzicoOptions();
        $options->setApiKey($this->getApiKey());
        $options->setSecretKey($this->getSecretKey());
        $options->setBaseUrl($this->getBaseUrl());

        return $options;
    }

    protected function mapLocale(string $locale): string
    {
        return match (strtoupper($locale)) {
            'EN' => IyzicoLocale::EN,
            default => IyzicoLocale::TR,
        };
    }

    protected function mapCurrency(string $currency): string
    {
        return match (strtoupper($currency)) {
            'TRY', 'TL' => IyzicoCurrency::TL,
            'USD' => IyzicoCurrency::USD,
            'EUR' => IyzicoCurrency::EUR,
            'GBP' => IyzicoCurrency::GBP,
            'RUB' => IyzicoCurrency::RUB,
            'IRR' => IyzicoCurrency::IRR,
            'NOK' => IyzicoCurrency::NOK,
            'CHF' => IyzicoCurrency::CHF,
            default => throw new InvalidRequestException('Unsupported currency: ' . $currency . '. Supported: TRY/USD/EUR/GBP/RUB/IRR/NOK/CHF'),
        };
    }

    protected function mapPaymentChannel(string $channel): string
    {
        return match (strtoupper($channel)) {
            'MOBILE' => IyzicoPaymentChannel::MOBILE,
            'MOBILE_WEB' => IyzicoPaymentChannel::MOBILE_WEB,
            default => IyzicoPaymentChannel::WEB,
        };
    }

    protected function mapPaymentGroup(string $group): string
    {
        return match (strtoupper($group)) {
            'LISTING' => IyzicoPaymentGroup::LISTING,
            'SUBSCRIPTION' => IyzicoPaymentGroup::SUBSCRIPTION,
            default => IyzicoPaymentGroup::PRODUCT,
        };
    }

    protected function buildBuyer(CreditCard $card): \Iyzipay\Model\Buyer
    {
        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId($this->getConversationId());
        $buyer->setName($card->getFirstName());
        $buyer->setSurname($card->getLastName());
        $buyer->setGsmNumber($card->getPhone());
        $buyer->setEmail($card->getEmail());
        $buyer->setIdentityNumber($this->getIdentityNumber());
        $buyer->setLastLoginDate(date('Y-m-d H:i:s'));
        $buyer->setRegistrationDate(date('Y-m-d H:i:s'));
        $buyer->setRegistrationAddress($card->getBillingAddress1());
        $buyer->setIp($this->getClientIp());
        $buyer->setCity($card->getBillingCity());
        $buyer->setCountry($card->getBillingCountry());
        $buyer->setZipCode($card->getBillingPostcode());

        return $buyer;
    }

    protected function buildShippingAddress(CreditCard $card): \Iyzipay\Model\Address
    {
        $address = new \Iyzipay\Model\Address();
        $address->setContactName($card->getShippingFirstName() . ' ' . $card->getShippingLastName());
        $address->setCity($card->getShippingCity());
        $address->setCountry($card->getShippingCountry());
        $address->setAddress($card->getShippingAddress1());
        $address->setZipCode($card->getShippingPostcode());

        return $address;
    }

    protected function buildBillingAddress(CreditCard $card): \Iyzipay\Model\Address
    {
        $address = new \Iyzipay\Model\Address();
        $address->setContactName($card->getBillingFirstName() . ' ' . $card->getBillingLastName());
        $address->setCity($card->getBillingCity());
        $address->setCountry($card->getBillingCountry());
        $address->setAddress($card->getBillingAddress1());
        $address->setZipCode($card->getBillingPostcode());

        return $address;
    }

    protected function buildPaymentCard(CreditCard $card): \Iyzipay\Model\PaymentCard
    {
        $paymentCard = new \Iyzipay\Model\PaymentCard();
        $paymentCard->setCardHolderName($card->getName());
        $paymentCard->setCardNumber($card->getNumber());
        $paymentCard->setExpireMonth($card->getExpiryMonth());
        $paymentCard->setExpireYear($card->getExpiryYear());
        $paymentCard->setCvc($card->getCvv());
        $paymentCard->setRegisterCard(0);

        return $paymentCard;
    }

    protected function buildBasketItems(): array
    {
        $basketItems = [];
        $items = $this->getItems();

        if (!empty($items)) {
            foreach ($items as $item) {
                $basketItem = new \Iyzipay\Model\BasketItem();
                $basketItem->setId($item->getName());
                $basketItem->setName($item->getName());
                $basketItem->setCategory1($item->getDescription() ?: 'Genel');
                $basketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
                $basketItem->setPrice($item->getPrice());
                $basketItems[] = $basketItem;
            }
        }

        if (empty($basketItems)) {
            $basketItem = new \Iyzipay\Model\BasketItem();
            $basketItem->setId('ITEM001');
            $basketItem->setName($this->getDescription() ?: 'Payment');
            $basketItem->setCategory1('Genel');
            $basketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
            $basketItem->setPrice($this->getAmount());
            $basketItems[] = $basketItem;
        }

        return $basketItems;
    }
}
