<?php

namespace Omnipay\WorldpayCGHosted\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    /** @var PurchaseRequest */
    private $purchase;

    public function setUp()
    {
        parent::setUp();

        $this->purchase = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->purchase->setAmount(7.45);
        $this->purchase->setCard(new CreditCard());
    }

    public function testPaymentMethodMaskWithKnownOmnipayType()
    {
        $this->purchase->setPaymentType('mastercard');
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
}
