<?php

namespace Omnipay\WorldpayCGHosted\Message;

use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Request;

/**
 * WorldPay XML Notification - not technically a response but shares
 * most of the same general XML payload structure.
 */
class Notification extends Response
{
    const RESPONSE_BODY_SUCCESS = '[OK]';       // Must exactly match Worldpay's stated body.
    const RESPONSE_CODE_SUCCESS = 200;          // Must be 200 for Worldpay.
    const RESPONSE_BODY_ERROR   = '[ERROR]';    // Arbitrary not-OK string.
    const RESPONSE_CODE_ERROR   = 500;
    /** @var string */
    private $originIp;

    public function __construct($request = null, $data = '', $notificationOriginIp)
    {
        $this->originIp = $notificationOriginIp;

        if ($request === null) {
            $request = new PurchaseRequest(new Client(), new Request());
        }

        parent::__construct($request, $data);
    }

    /**
     * Get the most recent Worldpay status string as of this notification,
     * e.g. AUTHORISED, CAPTURED, REFUSED, CANCELLED, ...
     *
     * @link http://bit.ly/wp-notification-statuses Which statuses trigger notifications
     * @link http://bit.ly/wp-status-detail         More detail on statuses
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->data->payment->lastEvent->__toString();
    }

    /**
     * @return bool
     */
    public function isAuthorised()
    {
        return ($this->isValid() && $this->isSuccessful());
    }

    /**
     * While this only checks the source host currently, it might include support for client TLS
     * verification or other checks in the future.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->originIsValid();
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
        $hostname = gethostbyaddr($this->originIp);
        if (!$hostname) { // Empty string or boolean false
            return false;
        }

        $expectedEnd = 'worldpay.com';
        $expectedPosition = strlen($hostname) - strlen($expectedEnd);

        return (strpos($hostname, $expectedEnd) === $expectedPosition);
    }
}
