<?php

namespace Omnipay\Iyzico\Tests\Message;

use Omnipay\Common\Message\RequestInterface;
use Omnipay\Iyzico\Message\RedirectResponse;
use PHPUnit\Framework\TestCase;

class RedirectResponseTest extends TestCase
{
    public function testIsRedirectWithHtmlContent(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'success', 'htmlContent' => '<form>...</form>']
        );

        $this->assertTrue($response->isRedirect());
    }

    public function testIsRedirectWithRedirectUrl(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );
        $response->setRedirectUrl('https://sandbox-api.iyzipay.com/payment');

        $this->assertTrue($response->isRedirect());
    }

    public function testIsRedirectWithoutHtmlContentAndWithoutUrlReturnsFalse(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $this->assertFalse($response->isRedirect());
    }

    public function testIsRedirectWithNonSuccessStatusReturnsFalse(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'failure', 'htmlContent' => '<form>...</form>']
        );

        $this->assertFalse($response->isRedirect());
    }

    public function testGetHtmlContentFromHtmlContentField(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            [
                'status' => 'success',
                'htmlContent' => '<form>3ds</form>',
                'checkoutFormContent' => '<div>checkout</div>',
            ]
        );

        $this->assertSame('<form>3ds</form>', $response->getHtmlContent());
    }

    public function testGetHtmlContentFallsBackToCheckoutFormContent(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            [
                'status' => 'success',
                'checkoutFormContent' => '<div>checkout</div>',
            ]
        );

        $this->assertSame('<div>checkout</div>', $response->getHtmlContent());
    }

    public function testGetHtmlContentReturnsNullWhenNeitherPresent(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $this->assertNull($response->getHtmlContent());
    }

    public function testSetAndGetRedirectUrl(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $response->setRedirectUrl('https://example.com/redirect');
        $this->assertSame('https://example.com/redirect', $response->getRedirectUrl());
    }

    public function testGetRedirectUrlDefaultsToEmptyString(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $this->assertSame('', $response->getRedirectUrl());
    }

    public function testSetAndGetRedirectMethod(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $response->setRedirectMethod('GET');
        $this->assertSame('GET', $response->getRedirectMethod());
    }

    public function testSetRedirectMethodIsUppercased(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $response->setRedirectMethod('get');
        $this->assertSame('GET', $response->getRedirectMethod());
    }

    public function testDefaultRedirectMethodIsPost(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $this->assertSame('POST', $response->getRedirectMethod());
    }

    public function testSetAndGetRedirectData(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $response->setRedirectData(['token' => 'abc123']);
        $this->assertSame(['token' => 'abc123'], $response->getRedirectData());
    }

    public function testDefaultRedirectDataIsEmptyArray(): void
    {
        $response = new RedirectResponse(
            $this->createMock(RequestInterface::class),
            ['status' => 'success']
        );

        $this->assertSame([], $response->getRedirectData());
    }
}
