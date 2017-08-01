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
    const SSL_ROOT = <<<EOT
-----BEGIN CERTIFICATE-----
MIIEmzCCA4OgAwIBAgIJALrqM7ceYPJnMA0GCSqGSIb3DQEBBQUAMIGPMQswCQYD
VQQGEwJHQjEXMBUGA1UECBMOQ2FtYnJpZGdlc2hpcmUxEjAQBgNVBAcTCUNhbWJy
aWRnZTEVMBMGA1UEChMMV29ybGRQYXkgTHRkMR8wHQYDVQQLExZBcHBsaWNhdGlv
biBNYW5hZ2VtZW50MRswGQYDVQQDExJXUEcgQ2xpZW50IFJvb3QgQ0EwHhcNMTMw
MjE0MTMzNzI4WhcNMzMwMjA5MTMzNzI4WjCBjzELMAkGA1UEBhMCR0IxFzAVBgNV
BAgTDkNhbWJyaWRnZXNoaXJlMRIwEAYDVQQHEwlDYW1icmlkZ2UxFTATBgNVBAoT
DFdvcmxkUGF5IEx0ZDEfMB0GA1UECxMWQXBwbGljYXRpb24gTWFuYWdlbWVudDEb
MBkGA1UEAxMSV1BHIENsaWVudCBSb290IENBMIIBIjANBgkqhkiG9w0BAQEFAAOC
AQ8AMIIBCgKCAQEA2wmQMuHZtkMllKnG7aa/Zk+mz7+FcE5crXBoOONnCZbdsFc6
MaVCDe+RIJ4iWWpGeI3IoP7no1pgoa4Lonnw2IW5q/c0qnY8OPWD8WwR/w/D68Ov
4Bsv6369VBbEyjrgTTZWWPJf2Sto4ytfJRDW9dw4YmhIl63UoAO0L8raNbyksmkD
w1VPbQMs6UZBpOF1RnjeSGondoSnAV9DXzmWyKuDM0AY4a0vKTL/u3L6HXJJojy0
KgLZhj0Vrvnv4FpHFB1VtDuUwzMDzJY+3HeQpvXiKFY0cZXyohWajKi14S7MmevV
BxBEDDwGWT+Goju58DagMI2TxGIjqFJYgjBSNwIDAQABo4H3MIH0MB0GA1UdDgQW
BBRK5xrSlGphp1SMV4rygihV0ByLmjCBxAYDVR0jBIG8MIG5gBRK5xrSlGphp1SM
V4rygihV0ByLmqGBlaSBkjCBjzELMAkGA1UEBhMCR0IxFzAVBgNVBAgTDkNhbWJy
aWRnZXNoaXJlMRIwEAYDVQQHEwlDYW1icmlkZ2UxFTATBgNVBAoTDFdvcmxkUGF5
IEx0ZDEfMB0GA1UECxMWQXBwbGljYXRpb24gTWFuYWdlbWVudDEbMBkGA1UEAxMS
V1BHIENsaWVudCBSb290IENBggkAuuoztx5g8mcwDAYDVR0TBAUwAwEB/zANBgkq
hkiG9w0BAQUFAAOCAQEAdDp8yE0UY+j/VK910oNEX8QA6aFRUz3RAJX8UXU0k3k9
H82awMDl68TKFMd04Ji/pNknyh5BYm1ZcuGRtkCA7uMaUioKMXmvhz7wwxgqJ74w
VPgkYpWb1qiIJBRFJVCh8gRuHZFLTajTNwIsKNCDjSVTcRBavwMnU6Uu5pOP6zo2
JyVJS5Wns7AI+jOcExXc9UwM3xQM7gpUQLBvJv7AdVcqbF4qcydBcyFE7MASjtIG
uvUQv6mYlarEWNHwFObN7orKnmVplP600ZhwiGadkfmPlzUwN7MtAu3fkYdySf/o
gjHGy1zRWxANZWTgVNq+Pgv1tZ3xJpfvVHgrWIbPSA==
-----END CERTIFICATE-----
EOT;
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
     * @return string E.g. AUTHORISED
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
     * @return bool
     */
    public function isValid()
    {
        return ($this->originIsValid() && $this->signatureIsValid());
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

    /**
     * @return bool
     */
    private function signatureIsValid()
    {
        // todo
    }
}
