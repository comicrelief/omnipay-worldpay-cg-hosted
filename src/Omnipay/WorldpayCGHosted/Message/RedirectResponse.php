<?php

namespace Omnipay\WorldpayCGHosted\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * WorldPay XML Redirect Response
 */
class RedirectResponse extends Response implements RedirectResponseInterface
{
    /** @var string|null */
    private $successUrl;
    /** @var string|null */
    private $failureUrl;
    /** @var string|null */
    private $cancelUrl;

    public function getRedirectData()
    {
        return [];
    }

    /**
     * Get redirect method
     *
     * @access public
     * @return string
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     * Get redirect url
     *
     * @access public
     * @return string
     */
    public function getRedirectUrl()
    {
        $url = $this->data->reference->__toString();

        // http://support.worldpay.com/support/kb/gg/corporate-gateway-guide/content/hostedintegration/paymentpageintegration.htm#Custom
        if (!empty($this->successUrl)) {
            $url .= '&successURL=' . rawurlencode($this->successUrl);
            $url .= '&pendingURL=' . rawurlencode($this->successUrl);
        }

        if (!empty($this->failureUrl)) {
            $url .= '&failureURL=' . rawurlencode($this->failureUrl);
            $url .= '&errorURL=' . rawurlencode($this->failureUrl);
        }

        if (!empty($this->cancelUrl)) {
            $url .= '&cancelURL=' . rawurlencode($this->cancelUrl);
        }

        return $url;
    }

    /**
     * @param string|null   $successUrl
     */
    public function setSuccessUrl($successUrl)
    {
        $this->successUrl = $successUrl;
    }

    /**
     * @param string|null   $failureUrl
     */
    public function setFailureUrl($failureUrl)
    {
        $this->failureUrl = $failureUrl;
    }
    /**
     * @param string|null   $cancelUrl
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
    }
}
