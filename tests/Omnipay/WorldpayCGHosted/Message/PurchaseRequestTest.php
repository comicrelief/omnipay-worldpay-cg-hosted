<?php

namespace Omnipay\WorldpayCGHosted\Tests\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Tests\TestCase;
use Omnipay\WorldpayCGHosted\Message\PurchaseRequest;

class PurchaseRequestTest extends TestCase
{
    /** @var PurchaseRequest */
    private $purchase;

    public function setUp()
    {
        parent::setUp();

        $this->purchase = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->purchase->setAmount(7.45);
        $this->purchase->setCurrency('GBP');
        $this->purchase->setCard(new CreditCard());
    }

    public function testAmountDetails()
    {
        $data = $this->purchase->getData();
        $this->assertEquals('745', $data->submit->order->amount->attributes()['value']);
        $this->assertEquals('GBP', $data->submit->order->amount->attributes()['currencyCode']);
        $this->assertEquals('2', $data->submit->order->amount->attributes()['exponent']);

        // Not included in a normal purchase request's data
        $this->assertEmpty($data->submit->order->session);
    }

    public function testDefaultDescription()
    {
        $data = $this->purchase->getData();
        $this->assertEquals('Merchandise', $data->submit->order->description);
    }

    public function testPaymentMethodMaskWithKnownOmnipayType()
    {
        $this->purchase->setPaymentType('mAstErcARd');  // gets lowercased for array key
        $data = $this->purchase->getData();
        $this->assertEquals('ECMC-SSL', $data->submit->order->paymentMethodMask->include->attributes()['code']);
    }

    public function testPaymentMethodMaskWithKnownWorldpayTypeThatAlsoHasOmnipayType()
    {
        $this->purchase->setPaymentType('MSCD');
        $data = $this->purchase->getData();
        $this->assertEquals('ECMC-SSL', $data->submit->order->paymentMethodMask->include->attributes()['code']);
    }

    public function testPaymentMethodMaskWithKnownWorldpayTypeAndNoOmnipayType()
    {
        $this->purchase->setPaymentType('VIED'); // Visa Electron
        $data = $this->purchase->getData();
        $this->assertEquals('VISA-SSL', $data->submit->order->paymentMethodMask->include->attributes()['code']);
    }

    public function testPaymentMethodMaskWithKnownWorldpaySSLKeyAndNoOnipayType()
    {
        $this->purchase->setPaymentType('ECMC');
        $data = $this->purchase->getData();
        $this->assertEquals('ECMC-SSL', $data->submit->order->paymentMethodMask->include->attributes()['code']);
    }

    public function testPaymentMethodMaskWithUnknownType()
    {
        $this->purchase->setPaymentType('Bitcoin'); // Visa Electron
        $data = $this->purchase->getData();
        $this->assertEquals('ALL', $data->submit->order->paymentMethodMask->include->attributes()['code']);
    }

    public function testCustomDescription()
    {
        $this->purchase->setDescription('Goods n services');
        $data = $this->purchase->getData();
        $this->assertEquals('Goods n services', $data->submit->order->description);
    }

    /**
     * This has not been tested against a real implementation, and may not be relevant for the Hosted gateway?
     * However this test covers the properties I'd expect to see from reading the wider XML API's docs.
     */
    public function test3DSecureResponse()
    {
        $this->purchase->setPaResponse('Some PA value');
        $this->purchase->setSession('vinces-session-token');
        $this->purchase->setClientIp('10.0.7.45');

        $data = $this->purchase->getData();

        $this->assertEquals('10.0.7.45', $data->submit->order->session->attributes()['shopperIPAddress']);
        $this->assertEquals('vinces-session-token', $data->submit->order->session->attributes()['id']);
        $this->assertEquals('Some PA value', $data->submit->order->info3DSecure->paResponse);
    }

    public function testAuxiliarySettersAndGetters()
    {
        $this->assertNull($this->purchase->getAcceptHeader());
        $this->assertNull($this->purchase->getPaResponse());
        $this->assertNull($this->purchase->getSession());
        $this->assertNull($this->purchase->getUserAgentHeader());

        $this->purchase->setAcceptHeader('text/xml');
        $this->purchase->setPaResponse('Some value');
        $this->purchase->setSession('my-token-key');
        $this->purchase->setUserAgentHeader('My great browser');

        $this->assertEquals('text/xml', $this->purchase->getAcceptHeader());
        $this->assertEquals('Some value', $this->purchase->getPaResponse());
        $this->assertEquals('my-token-key', $this->purchase->getSession());
        $this->assertEquals('My great browser', $this->purchase->getUserAgentHeader());
    }
}
