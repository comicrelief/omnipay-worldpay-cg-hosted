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

    /**
     * Get transaction reference
     *
     * @return string|null
     */
    public function getTransactionReference()
    {
        if (empty($this->data)) {
            return null;
        }

        $attributes = $this->data->attributes();

        if (isset($attributes['orderCode'])) {
            return $attributes['orderCode'];
        }

        return null;
    }

    /**
     * Get is successful
     *
     * @return bool
     */
    public function isSuccessful()
    {
        if (!isset($this->data->payment->lastEvent)) {
            return false;
        }

        return in_array(
            strtoupper($this->data->payment->lastEvent),
            [
                self::$PAYMENT_STATUS_AUTHORISED,
                self::$PAYMENT_STATUS_CAPTURED,
                self::$PAYMENT_STATUS_SETTLED_BY_MERCHANT,
            ],
            true
        );
    }

    public function isPending()
    {
        if (!isset($this->data->payment->lastEvent)) {
            return false;
        }

        return in_array(
            strtoupper($this->data->payment->lastEvent),
            [
                self::$PAYMENT_STATUS_SENT_FOR_AUTHORISATION,
            ],
            true
        );
    }
}
