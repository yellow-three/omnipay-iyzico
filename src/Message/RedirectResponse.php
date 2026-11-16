<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\RedirectResponseInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class RedirectResponse extends Response implements RedirectResponseInterface
{
    private string $redirectUrl = '';
    private string $redirectMethod = 'POST';
    private array $redirectData = [];

    public function setRedirectUrl(string $url): static
    {
        $this->redirectUrl = $url;
        return $this;
    }

    public function setRedirectMethod(string $method): static
    {
        $this->redirectMethod = strtoupper($method);
        return $this;
    }

    public function setRedirectData(array $data): static
    {
        $this->redirectData = $data;
        return $this;
    }

    public function isRedirect(): bool
    {
        return $this->getStatus() === 'success'
            && ($this->getRedirectUrl() !== '' || $this->getHtmlContent() !== null);
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    public function getRedirectMethod(): string
    {
        return $this->redirectMethod;
    }

    public function getRedirectData(): array
    {
        return $this->redirectData;
    }

    /**
     * 3DS (and BKM) initialization returns a ready-made, auto-submitting HTML page rather
     * than a URL. Omnipay's default implementation throws on an empty redirect URL, which
     * broke the standard `if ($response->isRedirect()) $response->redirect();` flow, so
     * serve that HTML as-is when there is no URL to redirect to.
     */
    public function getRedirectResponse()
    {
        $html = $this->getHtmlContent();

        if ($this->redirectUrl === '' && $html !== null && $this->isRedirect()) {
            return new HttpResponse($html);
        }

        return parent::getRedirectResponse();
    }

    public function getHtmlContent(): ?string
    {
        return $this->data['htmlContent'] ?? $this->data['checkoutFormContent'] ?? null;
    }
}
