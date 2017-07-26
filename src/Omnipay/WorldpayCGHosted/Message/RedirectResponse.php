<?php

namespace Omnipay\WorldpayCGHosted\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * WorldPay XML Redirect Response
 */
class RedirectResponse extends Response implements RedirectResponseInterface
{
    /**
     * Get redirect data
     *
     * @access public
     * @return array
     */
    public function getRedirectData()
    {
        return [
            'PaReq'   => $this->data->requestInfo->request3DSecure->paRequest,
            'TermUrl' => $this->request->getTermUrl()
        ];
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
        // TODO when we use this result we should be able to append e.g. &successURL=... GET params. Need to decide
        // where's best for this to live.

        return $this->data->reference->__toString();
    }
}
