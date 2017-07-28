<?php

namespace Omnipay\WorldpayCGHosted\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Omnipay WorldPay XML Purchase Request
 */
class PurchaseRequest extends AbstractRequest
{
    const API_HOST_LIVE = 'https://secure.worldpay.com';
    const API_HOST_TEST = 'https://secure-test.worldpay.com';

    const API_PATH = '/jsp/merchant/xml/paymentService.jsp';
    const API_VERSION = '1.4';

    /**
     * Get accept header
     *
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
     * @return AbstractRequest
     */
    public function setAcceptHeader($value)
    {
        return $this->setParameter('acceptHeader', $value);
    }

    /**
     * Get installation
     *
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
     * @return AbstractRequest
     */
    public function setInstallation($value)
    {
        return $this->setParameter('installation', $value);
    }

    /**
     * Get merchant
     *
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
     * @return AbstractRequest
     */
    public function setMerchant($value)
    {
        return $this->setParameter('merchant', $value);
    }

    /**
     * Get pa response
     *
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
     * @return AbstractRequest
     */
    public function setPaResponse($value)
    {
        return $this->setParameter('pa_response', $value);
    }

    /**
     * Get password
     *
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
     * @return AbstractRequest
     */
    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * Get session
     *
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
     * @return AbstractRequest
     */
    public function setSession($value)
    {
        return $this->setParameter('session', $value);
    }

    /**
     * Get user agent header
     *
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
     * @return AbstractRequest
     */
    public function setUserAgentHeader($value)
    {
        return $this->setParameter('userAgentHeader', $value);
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->getParameter('paymentType');
    }

    /**
     * @param string $value
     * @return AbstractRequest
     */
    public function setPaymentType($value)
    {
        return $this->setParameter('paymentType', $value);
    }

    /**
     * Get data
     *
     * @return \SimpleXMLElement
     */
    public function getData()
    {
        $this->validate('amount');

        $data = new \SimpleXMLElement('<paymentService />');
        $data->addAttribute('version', self::API_VERSION);
        $data->addAttribute('merchantCode', $this->getMerchant());

        $order = $data->addChild('submit')->addChild('order');

        $paResponse = $this->getPaResponse();

        if (empty($paResponse)) {
            $order->addAttribute('orderCode', $this->getTransactionId());
            $order->addAttribute('installationId', $this->getInstallation());

            $description = $this->getDescription() ? $this->getDescription() : 'Donation';
            $order->addChild('description', $description);

            $amount = $order->addChild('amount');
            $amount->addAttribute('value', $this->getAmountInteger());
            $amount->addAttribute('currencyCode', $this->getCurrency());
            $amount->addAttribute('exponent', $this->getCurrencyDecimalPlaces());

            $order->addChild('paymentMethodMask')->addChild('include')->addAttribute(
                'code',
                $this->getWorldpayPaymentType($this->getPaymentType())
            );

            $shopper = $order->addChild('shopper');
            $email = $this->getCard()->getEmail();
            if (!empty($email)) {
                $shopper->addChild('shopperEmailAddress', $email);
            }
            $browser = $shopper->addChild('browser');
            $browser->addChild('acceptHeader', $this->getAcceptHeader());
            $browser->addChild('userAgentHeader', $this->getUserAgentHeader());

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
        } else { // paResponse is set => the whole order contents should be (info3DSecure, session)
            $session = $order->addChild('session'); // Empty tag is valid but setting an empty ID attr isn't
            $session->addAttribute('shopperIPAddress', $this->getClientIP());
            $session->addAttribute('id', $this->getSession());

            $info3DSecure = $order->addChild('info3DSecure');
            $info3DSecure->addChild('paResponse', $paResponse);
        }

        return $data;
    }

    /**
     * Send data
     *
     * @param \SimpleXMLElement $data Data
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
     * @return string
     */
    protected function getEndpoint()
    {
        if ($this->getTestMode()) {
            return self::API_HOST_TEST . self::API_PATH;
        }

        return self::API_HOST_LIVE . self::API_PATH;
    }

    /**
     * @param string $input Omnipay card brand or 'raw' Worldpay payment type
     * @return string       Worldpay payment type's XML identifier
     */
    protected function getWorldpayPaymentType($input)
    {
        $codes = [
            CreditCard::BRAND_AMEX          => 'AMEX-SSL',
            CreditCard::BRAND_DANKORT       => 'DANKORT-SSL',
            CreditCard::BRAND_DINERS_CLUB   => 'DINERS-SSL',
            CreditCard::BRAND_DISCOVER      => 'DISCOVER-SSL',
            CreditCard::BRAND_JCB           => 'JCB-SSL',
            CreditCard::BRAND_LASER         => 'LASER-SSL',
            CreditCard::BRAND_MAESTRO       => 'MAESTRO-SSL',
            CreditCard::BRAND_MASTERCARD    => 'ECMC-SSL',
            CreditCard::BRAND_SWITCH        => 'MAESTRO-SSL',
            CreditCard::BRAND_VISA          => 'VISA-SSL',
            'DINS'                          => 'DINERS-SSL',    // Diners
            'LASR'                          => 'LASER-SSL',     // Laser
            'MAES'                          => 'MAESTRO-SSL',   // Maestro
            'MSCD'                          => 'ECMC-SSL',      // Mastercard
            'DMC'                           => 'ECMC-SSL',      // Mastercard Debit
            'VISD'                          => 'VISA-SSL',      // Visa Debit
            'VIED'                          => 'VISA-SSL',      // Visa Electron
        ];

        // First preference: Omnipay CreditCard brand constant match.
        if (isset($code[strtolower($input)])) {
            return $codes[strtolower($input)];
        }

        // Second preference: Worldpay payment type.
        // See https://support.worldpay.com/support/kb/bg/customisingadvanced/custa9102.html
        if (isset($codes[$input])) {
            return $codes[$input];
        }
        if (in_array($input . '-SSL', $codes, true)) {
            return $input . '-SSL';
        }

        // Anything else: don't mask payment methods.
        return 'ALL';
    }
}
