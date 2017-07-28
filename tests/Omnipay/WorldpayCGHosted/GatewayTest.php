<?php

namespace Omnipay\WorldpayCGHosted;

use Omnipay\Common\CreditCard;
use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    /** @var Gateway */
    protected $gateway;
    /** @var array */
    private $parameters;
    /** @var CreditCard */
    private $card;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway(
            $this->getHttpClient(),
            $this->getHttpRequest()
        );

        $this->parameters = [
            'amount' => 7.45,
            'currency' => 'GBP',
            'cancelUrl' => 'https://www.example.com/cancel',
            'notifyUrl' => 'https://www.example.com/ipn',
            'returnUrl' => 'https://www.example.com/success',
            'failureUrl' => 'https://www.example.com/failure',
            'paymentType' => 'VISA',
            'transactionId' => '11111111-0000-0000-0000-000011110000',
        ];

        $this->card = new CreditCard([
            'billingFirstName' => 'Vince',
            'billingLastName' => 'Staples',
            'shippingFirstName' => 'Vince',
            'shippingLastName' => 'Staples',
            'email' => 'cr+vs@noellh.com',
            'billingAddress1' => '745 THORNBURY CLOSE',
            'shippingAddress1' => '745 THORNBURY CLOSE',
            'billingAddress2' => '',
            'shippingAddress2' => '',
            'billingCity' => 'LONDON',
            'shippingCity' => 'LONDON',
            'billingCountry' => 'GB',
            'shippingCountry' => 'GB',
            'billingPostcode' => 'N16 8UX',
            'shippingPostcode' => 'N16 8UX',
        ]);
    }

    public function testPurchaseSuccess()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');

        $purchase = $this->gateway->purchase($this->parameters);
        $purchase->setCard($this->card);
        $purchase->setTestMode(true);
        $response = $purchase->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('T0211010', $response->getTransactionReference());
    }

    public function testPurchaseError()
    {
        $this->setMockHttpResponse('PurchaseFailure.txt');

        $purchase = $this->gateway->purchase($this->parameters);
        $purchase->setCard($this->card);
        $purchase->setTestMode(true);
        $response = $purchase->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('CARD EXPIRED', $response->getMessage());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidCreditCardException
     */
    public function testMissingCard()
    {
        $purchase = $this->gateway->purchase($this->parameters);
        $purchase->setTestMode(true);
        $purchase->send();
    }

    public function testPurchaseRequestDataSetUp()
    {
        $purchase = $this->gateway->purchase($this->parameters);
        $purchase->setTestMode(true);
        $purchase->setInstallation('ABC123');
        $purchase->setMerchant('ACMECO');
        $purchase->setCard($this->card);

        /** @var \SimpleXMLElement $data */
        $data = $purchase->getData();
        /** @var \SimpleXMLElement $order */
        $order = $data->submit->order;

        $this->assertInstanceOf(\SimpleXMLElement::class, $data);
        $this->assertEquals('1.4', $data->attributes()['version']);
        $this->assertEquals('ACMECO', $data->attributes()['merchantCode']);
        $this->assertEquals($this->parameters['transactionId'], $order->attributes()['orderCode']);
        $this->assertEquals('ABC123', $order->attributes()['installationId']);
        $this->assertEquals('Donation', $order->description);
        $this->assertEquals('745', $order->amount->attributes()['value']);
        $this->assertEquals('GBP', $order->amount->attributes()['currencyCode']);
        $this->assertEquals('2', $order->amount->attributes()['exponent']);
        $this->assertEquals('VISA-SSL', $order->paymentMethodMask->include->attributes()['code']);
        $this->assertEquals('cr+vs@noellh.com', $order->shopper->shopperEmailAddress);
        $this->assertEquals('Vince', $order->billingAddress->address->firstName);
        $this->assertEquals('Staples', $order->billingAddress->address->lastName);
        $this->assertEquals('745 THORNBURY CLOSE', $order->billingAddress->address->address1);
        $this->assertEquals('', $order->billingAddress->address->address2);
        $this->assertEquals('N16 8UX', $order->billingAddress->address->postalCode);
        $this->assertEquals('LONDON', $order->billingAddress->address->city);
        $this->assertEquals('', $order->billingAddress->address->state);
        $this->assertEquals('GB', $order->billingAddress->address->countryCode);
    }
}
