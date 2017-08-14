<?php

namespace Omnipay\WorldpayCGHosted\Message;

use DOMDocument;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractResponse;

/**
 * WorldPay XML Notification - not technically a response but shares
 * most of the same general XML payload structure.
 */
class Notification extends AbstractResponse
{
    use ResponseTrait;

    const RESPONSE_BODY_SUCCESS = '[OK]';       // Must exactly match Worldpay's stated body.
    const RESPONSE_CODE_SUCCESS = 200;          // Must be 200 for Worldpay.
    const RESPONSE_BODY_ERROR   = '[ERROR]';    // Arbitrary not-OK string.
    const RESPONSE_CODE_ERROR   = 500;
    /** @var string */
    private $originIp;

    /** @noinspection PhpMissingParentConstructorInspection
     * @param string                $data
     * @param string                $notificationOriginIp
     * @throws InvalidResponseException on missing data
     */
    public function __construct($data, $notificationOriginIp)
    {
        $this->originIp = $notificationOriginIp;

        if (empty($data)) {
            throw new InvalidResponseException();
        }

        $responseDom = new DOMDocument;
        if (!@$responseDom->loadXML($data)) {
            throw new InvalidResponseException('Non-XML notification body received');
        }

        $document = simplexml_import_dom($responseDom->documentElement);
        $this->data = $document->notify->orderStatusEvent;
    }

    /**
     * Get the most recent Worldpay status string as of this notification,
     * e.g. AUTHORISED, CAPTURED, REFUSED, CANCELLED, ...
     *
     * @link http://bit.ly/wp-notification-statuses Which statuses trigger notifications
     * @link http://bit.ly/wp-status-detail         More detail on statuses
     *
     * @return string|null
     */
    public function getStatus()
    {
        if (!$this->hasStatus()) {
            return null;
        }

        return $this->data->payment->lastEvent->__toString();
    }

    /**
     * @return bool
     */
    public function hasStatus()
    {
        return !empty($this->data->payment->lastEvent);
    }

    /**
     * @return bool
     */
    public function isAuthorised()
    {
        return ($this->isValid() && $this->isSuccessful());
    }

    /**
     * While this only checks the source host and data structure currently, it might include support for client TLS
     * verification or other checks in the future.
     *
     * @return bool
     */
    public function isValid()
    {
        return ($this->originIsValid() && $this->hasStatus());
    }

    /**
     * Gets the body of the response your app should provide to the Worldpay bot for this request.
     *
     * @return string
     */
    public function getResponseBody()
    {
        return (
            $this->isValid() ?
                self::RESPONSE_BODY_SUCCESS :
                self::RESPONSE_BODY_ERROR
        );
    }

    /**
     * Gets the HTTP response status code your app should provide to the Worldpay bot for this request.
     *
     * @return int
     */
    public function getResponseStatusCode()
    {
        return (
            $this->isValid() ?
                self::RESPONSE_CODE_SUCCESS :
                self::RESPONSE_CODE_ERROR
        );
    }

    /**
     * Indicates whether the given origin IP address matches *.worldpay.com based on reverse DNS.
     *
     * @return bool
     */
    private function originIsValid()
    {
        if (empty($this->originIp)) {
            return false;
        }

        $hostname = @gethostbyaddr($this->originIp);
        if (!$hostname) { // Empty string or boolean false
            return false;
        }

        $expectedEnd = 'worldpay.com';
        $expectedPosition = strlen($hostname) - strlen($expectedEnd);

        return (strpos($hostname, $expectedEnd) === $expectedPosition);
    }
}
