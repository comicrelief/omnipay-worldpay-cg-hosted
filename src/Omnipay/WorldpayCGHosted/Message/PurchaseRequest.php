<?php

namespace Omnipay\WorldpayCGHosted\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * Omnipay WorldPay XML Purchase Request
 */
class PurchaseRequest extends AbstractRequest
{
    const EP_HOST_LIVE = 'https://secure.worldpay.commmmm.i.will.fail'; // todo
    const EP_HOST_TEST = 'https://secure-test.worldpay.com';

    const EP_PATH = '/jsp/merchant/xml/paymentService.jsp';

    const VERSION = '1.4';

    /**
     * Get accept header
     *
     * @access public
     * @return string
     */
    public function getAcceptHeader()
    {
        return $this->getParameter('acceptHeader');
    }

    /**
     * Set accept header
     *
     * @param string $value Accept header
     *
     * @access public
     * @return void
     */
    public function setAcceptHeader($value)
    {
        return $this->setParameter('acceptHeader', $value);
    }

    /**
     * Get installation
     *
     * @access public
     * @return string
     */
    public function getInstallation()
    {
        return $this->getParameter('installation');
    }

    /**
     * Set installation
     *
     * @param string $value Installation
     *
     * @access public
     * @return void
     */
    public function setInstallation($value)
    {
        return $this->setParameter('installation', $value);
    }

    /**
     * Get merchant
     *
     * @access public
     * @return string
     */
    public function getMerchant()
    {
        return $this->getParameter('merchant');
    }

    /**
     * Set merchant
     *
     * @param string $value Merchant
     *
     * @access public
     * @return void
     */
    public function setMerchant($value)
    {
        return $this->setParameter('merchant', $value);
    }

    /**
     * Get pa response
     *
     * @access public
     * @return string
     */
    public function getPaResponse()
    {
        return $this->getParameter('pa_response');
    }

    /**
     * Set pa response
     *
     * @param string $value Pa response
     *
     * @access public
     * @return void
     */
    public function setPaResponse($value)
    {
        return $this->setParameter('pa_response', $value);
    }

    /**
     * Get password
     *
     * @access public
     * @return string
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    /**
     * Set password
     *
     * @param string $value Password
     *
     * @access public
     * @return void
     */
    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * Get session
     *
     * @access public
     * @return string
     */
    public function getSession()
    {
        return $this->getParameter('session');
    }

    /**
     * Set session
     *
     * @param string $value Session
     *
     * @access public
     * @return void
     */
    public function setSession($value)
    {
        return $this->setParameter('session', $value);
    }

    /**
     * Get term url
     *
     * @access public
     * @return string
     */
    public function getTermUrl()
    {
        return $this->getParameter('termUrl');
    }

    /**
     * Set term url
     *
     * @param string $value Term url
     *
     * @access public
     * @return void
     */
    public function setTermUrl($value)
    {
        return $this->setParameter('termUrl', $value);
    }

    /**
     * Get user agent header
     *
     * @access public
     * @return string
     */
    public function getUserAgentHeader()
    {
        return $this->getParameter('userAgentHeader');
    }

    /**
     * Set user agent header
     *
     * @param string $value User agent header
     *
     * @access public
     * @return void
     */
    public function setUserAgentHeader($value)
    {
        return $this->setParameter('userAgentHeader', $value);
    }

    /**
     * Get data
     *
     * @access public
     * @return \SimpleXMLElement
     */
    public function getData()
    {
        $this->validate('amount');
//        $this->getCard()->validate();

        $data = new \SimpleXMLElement('<paymentService />');
        $data->addAttribute('version', self::VERSION);
        $data->addAttribute('merchantCode', $this->getMerchant());

        $order = $data->addChild('submit')->addChild('order');
        $order->addAttribute('orderCode', $this->getTransactionId());
        $order->addAttribute('installationId', $this->getInstallation());

        $description = $this->getDescription() ? $this->getDescription() : 'Donation';
        $order->addChild('description', $description);

        $amount = $order->addChild('amount');
        $amount->addAttribute('value', $this->getAmountInteger());
        $amount->addAttribute('currencyCode', $this->getCurrency());
        $amount->addAttribute('exponent', $this->getCurrencyDecimalPlaces());


        $order->addChild('paymentMethodMask')->addChild('include')->addAttribute('code', 'ALL');

//        echo 'card brand ' . print_r($this->getCard());

//        $card = $payment->addChild($codes[$this->getCard()->getBrand()]);
//        $card->addChild('cardNumber', $this->getCard()->getNumber());

//        $expiry = $card->addChild('expiryDate')->addChild('date');
//        $expiry->addAttribute('month', $this->getCard()->getExpiryDate('m'));
//        $expiry->addAttribute('year', $this->getCard()->getExpiryDate('Y'));

//        $card->addChild('cardHolderName', $this->getCard()->getName());

//        if (
//            $this->getCard()->getBrand() == CreditCard::BRAND_MAESTRO
//            || $this->getCard()->getBrand() == CreditCard::BRAND_SWITCH
//        ) {
//            $start = $card->addChild('startDate')->addChild('date');
//            $start->addAttribute('month', $this->getCard()->getStartDate('m'));
//            $start->addAttribute('year', $this->getCard()->getStartDate('Y'));
//
//            $card->addChild('issueNumber', $this->getCard()->getIssueNumber());
//        }
//
//        $card->addChild('cvc', $this->getCard()->getCvv());

        $shopper = $order->addChild('shopper');
        $email = $this->getCard()->getEmail();
        if (!empty($email)) {
            $shopper->addChild('shopperEmailAddress', $email);
        }

        if ($this->getCard()) {
            $address = $order->addChild('billingAddress')->addChild('address');
            $address->addChild('firstName', $this->getCard()->getFirstName());
            $address->addChild('lastName', $this->getCard()->getLastName());
            $address->addChild('address1', $this->getCard()->getAddress1());
            $address->addChild('address2', $this->getCard()->getAddress2());
            $address->addChild('postalCode', $this->getCard()->getPostcode());
            $address->addChild('city', $this->getCard()->getCity());
            $address->addChild('state', $this->getCard()->getState());
            $address->addChild('countryCode', $this->getCard()->getCountry());
        }


        $paResponse = $this->getPaResponse();


        // TODO if paResponse is not empty, the whole order contents should be (info3DSecure, session) instead of everything else
//        if (empty($paResponse)) {
//            $session = $order->addChild('session'); // Empty tag is valid but setting an empty ID attr isn't
//            $session->addAttribute('shopperIPAddress', $this->getClientIP());
//            $session->addAttribute('id', $this->getSession());
//        } else {
//            $info3DSecure = $order->addChild('info3DSecure');
//            $info3DSecure->addChild('paResponse', $paResponse);
//        }



        $browser = $shopper->addChild('browser');
        $browser->addChild('acceptHeader', $this->getAcceptHeader());
        $browser->addChild('userAgentHeader', $this->getUserAgentHeader());

        return $data;
    }

    /**
     * Send data
     *
     * @param \SimpleXMLElement $data Data
     *
     * @access public
     * @return RedirectResponse
     */
    public function sendData($data)
    {
        $implementation = new \DOMImplementation();

        $dtd = $implementation->createDocumentType(
            'paymentService',
            '-//WorldPay//DTD WorldPay PaymentService v1//EN',
            'http://dtd.worldpay.com/paymentService_v1.dtd'
        );

        $document = $implementation->createDocument(null, '', $dtd);
        $document->encoding = 'utf-8';

        $node = $document->importNode(dom_import_simplexml($data), true);
        $document->appendChild($node);

        $authorisation = base64_encode(
            $this->getMerchant() . ':' . $this->getPassword()
        );

        $headers = [
            'Authorization' => 'Basic ' . $authorisation,
            'Content-Type'  => 'text/xml; charset=utf-8'
        ];

        $xml = $document->saveXML();

        $httpResponse = $this->httpClient
            ->post($this->getEndpoint(), $headers, $xml)
            ->send();


//        echo PHP_EOL . 'REQUEST' . PHP_EOL;
//
//        echo $xml;
//
//        echo PHP_EOL . 'RESPONSE' . PHP_EOL;
//
//        print_r($httpResponse->getHeaders());
//        echo $httpResponse->getBody();
//        echo PHP_EOL . 'END RESPONSE' . PHP_EOL;
//        exit;



        $this->response = new RedirectResponse(
            $this,
            $httpResponse->getBody()
        );

        $this->response->setSuccessUrl($this->getParameter('returnUrl'));
        $this->response->setFailureUrl($this->getParameter('failureUrl'));
        $this->response->setCancelUrl($this->getParameter('cancelUrl'));

        return $this->response;
    }

    /**
     * Get endpoint
     *
     * Returns endpoint depending on test mode
     *
     * @access protected
     * @return string
     */
    protected function getEndpoint()
    {
        if ($this->getTestMode()) {
            return self::EP_HOST_TEST . self::EP_PATH;
        }

        return self::EP_HOST_LIVE . self::EP_PATH;
    }
}
