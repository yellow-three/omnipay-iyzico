<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

class AcceptNotificationRequest extends BaseAbstractRequest implements NotificationInterface
{
    /**
     * iyzico mdStatus values:
     * 1 = 3DS authentication successful
     * 0 = 3DS authentication failed / not attempted
     * 2 = Card holder not registered to 3DS
     * 3 = Integration not configured for 3DS
     * 4 = System error
     * 5 = Card type not supported for 3DS
     * 6 = Card holder not registered for 3DS (card network side)
     */
    private const MD_STATUS_SUCCESS = 1;

    /**
     * setData() accepts the raw callback parameters (e.g. from $_POST).
     * This is NOT the standard Omnipay getData/sendData — AcceptNotification
     * receives an incoming webhook and determines the payment status.
     */
    public function initialize(array $parameters = [])
    {
        // Extract known parameters for parent initialize, store the rest as notification data.
        $notificationData = [];

        foreach ($parameters as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (!method_exists($this, $method)) {
                $notificationData[$key] = $value;
            }
        }

        $this->data = $notificationData;

        return parent::initialize($parameters);
    }

    /**
     * Override setParameter to route unknown keys into the data array.
     */
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
        return $this->data['paymentId'] ?? null;
    }

    /**
     * Determine the transaction status based on the callback data.
     *
     * For 3DS callbacks (mdStatus present):
     * - mdStatus=1 + status=success → STATUS_COMPLETED
     * - mdStatus=1 + status=failure → STATUS_FAILED
     * - mdStatus!=1 → STATUS_FAILED
     *
     * For non-3DS callbacks (CheckoutForm/PWI):
     * - status=success → STATUS_COMPLETED
     * - status=pending → STATUS_PENDING
     * - otherwise → STATUS_FAILED
     */
    public function getTransactionStatus(): string
    {
        $data = $this->getData();

        if (empty($data)) {
            return NotificationInterface::STATUS_FAILED;
        }

        // Check mdStatus for 3DS callbacks
        if (isset($data['mdStatus'])) {
            $mdStatus = (int) $data['mdStatus'];

            if ($mdStatus !== self::MD_STATUS_SUCCESS) {
                return NotificationInterface::STATUS_FAILED;
            }
        }

        // Check status field
        $status = $data['status'] ?? '';

        return match (strtolower($status)) {
            'success' => NotificationInterface::STATUS_COMPLETED,
            'pending' => NotificationInterface::STATUS_PENDING,
            default => NotificationInterface::STATUS_FAILED,
        };
    }

    /**
     * Return a human-readable message based on the callback data.
     */
    public function getMessage(): ?string
    {
        $data = $this->getData();

        if (isset($data['errorMessage'])) {
            $msg = $data['errorMessage'];
            if (isset($data['errorCode'])) {
                $msg .= ' (errorCode: ' . $data['errorCode'] . ')';
            }
            return $msg;
        }

        $status = $data['status'] ?? null;

        return match ($status) {
            'success' => 'Payment completed successfully',
            'pending' => 'Payment is pending',
            default => 'Unknown payment status',
        };
    }
}
