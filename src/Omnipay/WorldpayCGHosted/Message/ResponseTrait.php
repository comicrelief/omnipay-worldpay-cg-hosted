<?php

namespace Omnipay\WorldpayCGHosted\Message;

/**
 * Encapsulates response-like behaviour shared between actual Worldpay response objects and notifications, which
 * actually come in Worldpay-initiated requests.
 */
trait ResponseTrait
{
    /** @var string */
    protected static $PAYMENT_STATUS_AUTHORISED             = 'AUTHORISED';
    /** @var string */
    protected static $PAYMENT_STATUS_CAPTURED               = 'CAPTURED';
    /** @var string */
    protected static $PAYMENT_STATUS_SETTLED_BY_MERCHANT    = 'SETTLED_BY_MERCHANT';
    /** @var string */
    protected static $PAYMENT_STATUS_SENT_FOR_AUTHORISATION = 'SENT_FOR_AUTHORISATION';
    /** @var string */
    protected static $PAYMENT_STATUS_CANCELLED              = 'CANCELLED';

    /**
     * Get transaction reference provided with order, and sent back with notifications
     *
     * @return string|null
     */
    public function getTransactionId()
    {
        if (empty($this->getOrder())) {
            return null;
        }

        $attributes = $this->getOrder()->attributes();

        if (isset($attributes['orderCode'])) {
            return (string) $attributes['orderCode'];
        }

        return null;
    }

    /**
     * Whether transaction's last state indicates success
     *
     * @return bool
     */
    public function isSuccessful()
    {
        if (!isset($this->getOrder()->payment->lastEvent)) {
            return false;
        }

        return in_array(
            strtoupper($this->getOrder()->payment->lastEvent),
            [
                self::$PAYMENT_STATUS_AUTHORISED,
                self::$PAYMENT_STATUS_CAPTURED,
                self::$PAYMENT_STATUS_SETTLED_BY_MERCHANT,
            ],
            true
        );
    }

    /**
     * Whether transaction's last state was pending
     *
     * @return bool
     */
    public function isPending()
    {
        if (!isset($this->getOrder()->payment->lastEvent)) {
            return false;
        }

        return in_array(
            strtoupper($this->getOrder()->payment->lastEvent),
            [
                self::$PAYMENT_STATUS_SENT_FOR_AUTHORISATION,
            ],
            true
        );
    }

    /**
     * Whether transaction's last state indicates the user cancelled it
     *
     * @return bool
     */
    public function isCancelled()
    {
        if (!isset($this->getOrder()->payment->lastEvent)) {
            return false;
        }

        return in_array(
            strtoupper($this->getOrder()->payment->lastEvent),
            [
                self::$PAYMENT_STATUS_CANCELLED,
            ],
            true
        );
    }

    public function getOrder()
    {
        if (isset($this->data->orderStatusEvent)) {
            return $this->data->orderStatusEvent; // Notifications
        }

        if (isset($this->data->orderStatus)) {
            return $this->data->orderStatus; // Order responses
        }

        return null;
    }
}
