<?php

namespace Omnipay\WorldpayCGHosted\Tests\Message;

use Omnipay\Tests\TestCase;
use Omnipay\WorldpayCGHosted\Message\Response;

class ResponseTest extends TestCase
{
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     */
    public function testConstructEmpty()
    {
        new Response($this->getMockRequest(), '');
    }

    public function testPurchaseSuccessRedirect()
    {
        $httpResponse = $this->getMockHttpResponse('InitSuccessRedirect.txt');
        $response = new Response(
            $this->getMockRequest(),
            $httpResponse->getBody()
        );

        $this->assertTrue($response->isRedirect());
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('11001100-0000-0000-0000-000011110101', $response->getTransactionId());
        $this->assertEquals('PENDING', $response->getMessage());
    }

    /**
     * Not expected with Hosted redirect flow, but let's test the values are mapped correctly just in case, based
     * on the general XML API's format.
     */
    public function testPurchaseSuccessComplete()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseSuccess.txt');
        $response = new Response(
            $this->getMockRequest(),
            $httpResponse->getBody()
        );

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('T0211010', $response->getTransactionId());
        $this->assertEquals('AUTHORISED', $response->getMessage());
    }

    public function testPurchaseFailure()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseFailure.txt');
        $response = new Response(
            $this->getMockRequest(),
            $httpResponse->getBody()
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('T0211234', $response->getTransactionId());
        $this->assertSame('CARD EXPIRED', $response->getMessage());
    }

    public function testPurchaseError()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseError.txt');

        $response = new Response(
            $this->getMockRequest(),
            $httpResponse->getBody()
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('T0211234', $response->getTransactionId());
        $this->assertSame('ERROR: Nasty internal error!', $response->getMessage());
    }
}
