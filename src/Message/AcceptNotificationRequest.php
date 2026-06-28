<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\NotificationInterface;

class AcceptNotificationRequest extends AbstractRequest implements NotificationInterface
{
    public function initialize(array $parameters = [])
    {
        $notificationData = [];

        foreach ($parameters as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (!method_exists($this, $method)) {
                $notificationData[$key] = $value;
            }
        }

        parent::initialize($parameters);
        $this->data = $notificationData;

        return $this;
    }

    public function setNotificationData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getNotificationData(): array
    {
        return $this->data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function sendData($data): static
    {
        return $this;
    }

    public function send(): static
    {
        return $this->sendData($this->getData());
    }

    public function getTransactionReference(): ?string
    {
        $data = $this->getData();

        return $data['paymentId'] ?? $data['iyziPaymentId'] ?? $data['token'] ?? $this->getParameter('paymentId') ?? $this->getParameter('token') ?? null;
    }

    public function getTransactionStatus(): string
    {
        $data = $this->getData();

        if (empty($data)) {
            return NotificationInterface::STATUS_FAILED;
        }

        $status = strtoupper($data['status'] ?? '');

        return match ($status) {
            'SUCCESS' => NotificationInterface::STATUS_COMPLETED,
            'FAILURE' => NotificationInterface::STATUS_FAILED,
            default => NotificationInterface::STATUS_PENDING,
        };
    }

    public function getMessage(): ?string
    {
        $data = $this->getData();

        if (empty($data)) {
            return 'No notification data received';
        }

        return sprintf('Payment status: %s (event: %s)', $data['status'] ?? 'UNKNOWN', $data['iyziEventType'] ?? 'UNKNOWN');
    }

    public function getSignature(): string
    {
        return $this->getParameter('signature');
    }

    public function setSignature(string $value): static
    {
        return $this->setParameter('signature', $value);
    }

    public function isValid(): bool
    {
        $secretKey = $this->getParameter('secretKey');
        $data = $this->getData();

        if (empty($secretKey) || empty($data)) {
            return false;
        }

        $iyziEventType = $data['iyziEventType'] ?? '';
        $paymentConversationId = $data['paymentConversationId'] ?? '';
        $status = $data['status'] ?? '';

        // HPP format is identified by the presence of a `token` parameter.
        // `token` has a setter on Omnipay Common's AbstractRequest, so it lives
        // in the ParameterBag, not in notification data ($this->data).
        // Direct format does NOT have a `token` field.
        $isHpp = !empty($this->getParameter('token'));

        if ($isHpp) {
            $message = $secretKey
                . $iyziEventType
                . ($data['iyziPaymentId'] ?? '')
                . $this->getParameter('token')
                . $paymentConversationId
                . $status;
        } else {
            // `paymentId` has a setter on our AbstractRequest, so it also lives
            // in the ParameterBag. Fall back to $data for direct array input.
            $paymentId = $data['paymentId'] ?? $this->getParameter('paymentId') ?? '';
            $message = $secretKey
                . $iyziEventType
                . $paymentId
                . $paymentConversationId
                . $status;
        }

        $computed = hash_hmac('sha256', $message, $secretKey);

        return hash_equals($computed, (string) $this->getSignature());
    }
}
