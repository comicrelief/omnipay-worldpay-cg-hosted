<?php

namespace Omnipay\WorldpayCGHosted\Tests;

use Omnipay\Common\CreditCard;
use Omnipay\Tests\GatewayTestCase;
use Omnipay\WorldpayCGHosted\Gateway;

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
        $this->gateway->setMerchant('ACMECO');
        $this->gateway->setTestMode(true);
        $this->gateway->setInstallation('ABC123');

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
        $this->setMockHttpResponse('InitSuccessRedirect.txt');

        $purchase = $this->gateway->purchase($this->parameters);
        $purchase->setCard($this->card);
        $response = $purchase->send();

        $this->assertTrue($response->isRedirect());
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('11001100-0000-0000-0000-000011110101', $response->getTransactionId());
        $this->assertEquals('7457457457', $response->getTransactionReference());
    }

    public function testPurchaseError()
    {
        $this->setMockHttpResponse('PurchaseFailure.txt');

        $purchase = $this->gateway->purchase($this->parameters);
        $purchase->setCard($this->card);
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
        $purchase->send();
    }

    public function testPurchaseRequestDataSetUp()
    {
        $purchase = $this->gateway->purchase($this->parameters);
        $purchase->setCard($this->card);

        // Confirm basic auth uses merchant code to authenticate when there's no username.
        $this->assertEquals('ACMECO', $purchase->getUsername());

        /** @var \SimpleXMLElement $data */
        $data = $purchase->getData();
        /** @var \SimpleXMLElement $order */
        $order = $data->submit->order;

        $this->assertInstanceOf(\SimpleXMLElement::class, $data);
        $this->assertEquals('1.4', $data->attributes()['version']);
        $this->assertEquals('ACMECO', $data->attributes()['merchantCode']);
        $this->assertEquals($this->parameters['transactionId'], $order->attributes()['orderCode']);
        $this->assertEquals('ABC123', $order->attributes()['installationId']);
        $this->assertEquals('Merchandise', $order->description);
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

        $this->assertEquals($this->parameters['returnUrl'], $purchase->getReturnUrl());
        $this->assertEquals($this->parameters['failureUrl'], $purchase->getFailureUrl());
        $this->assertEquals($this->parameters['cancelUrl'], $purchase->getCancelUrl());
        $this->assertEquals($this->parameters['notifyUrl'], $purchase->getNotifyUrl());
    }

    /**
     * Confirm basic auth uses a username when set rather than merchant code.
     */
    public function testUsernameAuthSetup()
    {
        $gatewayWithUsername = clone $this->gateway;
        $gatewayWithUsername->setUsername('MYSECRETUSERNAME987');

        $purchase = $gatewayWithUsername->purchase($this->parameters);
        $purchase->setCard($this->card);

        $this->assertEquals('MYSECRETUSERNAME987', $purchase->getUsername());
    }

    /**
     * Billing address is required with default settings
     *
     * @expectedException \Omnipay\Common\Exception\InvalidCreditCardException
     * @expectedExceptionMessage A billing address is required for this transaction
     */
    public function testMissingAddressWithDefaultLogic()
    {
        $cardWithoutAddress = new CreditCard([
            'email' => 'cr+vs@noellh.com',
        ]);

        $purchase = $this->gateway->purchase($this->parameters);
        $purchase->setCard($cardWithoutAddress);

        $purchase->getData();
    }

    public function testAuxiliarySettersAndGetters()
    {
        $this->assertNull($this->gateway->getAcceptHeader());
        $this->assertNull($this->gateway->getPaResponse());
        $this->assertNull($this->gateway->getSession());
        $this->assertNull($this->gateway->getUserAgentHeader());
        $this->assertNull($this->gateway->getUserIP());

        $this->gateway->setAcceptHeader('text/xml');
        $this->gateway->setPaResponse('Some value');
        $this->gateway->setSession('my-token-key');
        $this->gateway->setUserAgentHeader('My great browser');
        $this->gateway->setUserIP('10.0.0.99');

        $this->assertEquals('text/xml', $this->gateway->getAcceptHeader());
        $this->assertEquals('Some value', $this->gateway->getPaResponse());
        $this->assertEquals('my-token-key', $this->gateway->getSession());
        $this->assertEquals('My great browser', $this->gateway->getUserAgentHeader());
        $this->assertEquals('10.0.0.99', $this->gateway->getUserIP());
    }

    public function testUsernameParam()
    {
        // when username is not set
        $this->assertNull($this->gateway->getParameter('username'));
        // test that username falls back to merchant
        $this->assertEquals($this->gateway->getParameter('merchant'), $this->gateway->getUsername());

        // verify username parameter set works
        $this->gateway->setParameter('username', 'test_username');
        $this->assertEquals('test_username', $this->gateway->getUsername());
    }
}
