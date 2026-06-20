<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

class RedirectResponse extends Response implements RedirectResponseInterface
{
    private string $redirectUrl;
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
        return true;
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

    public function getHtmlContent(): ?string
    {
        return $this->data['htmlContent'] ?? $this->data['checkoutFormContent'] ?? null;
    }
}
