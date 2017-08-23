<?php

namespace Omnipay\WorldpayCGHosted\Tests\Message;

use Omnipay\Tests\TestCase;
use Omnipay\WorldpayCGHosted\Message\Response;

class ResponseTest extends TestCase
{
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     * @expectedExceptionMessage Empty data received
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
        $this->assertInternalType('string', $response->getTransactionId());
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

    public function testPurchaseErrorGeneric()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseErrorGeneric.txt');

        $response = new Response(
            $this->getMockRequest(),
            $httpResponse->getBody()
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ERROR: Nasty internal error!', $response->getMessage());
        $this->assertNull($response->getErrorCode());
    }

    public function testPurchaseErrorDuplicateOrder()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseErrorDuplicateOrder.txt');

        $response = new Response(
            $this->getMockRequest(),
            $httpResponse->getBody()
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ERROR: Duplicate Order', $response->getMessage());
        $this->assertSame('5', $response->getErrorCode());
    }

    /**
     * You can get this e.g. if you are authenticated but your merchant code in the body is wrong.
     */
    public function testPurchaseErrorSecurityFailure()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseErrorSecurityViolation.txt');

        $response = new Response(
            $this->getMockRequest(),
            $httpResponse->getBody()
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ERROR: Security violation', $response->getMessage());
        $this->assertSame('4', $response->getErrorCode());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     * @expectedExceptionMessage Could not import response XML:
     */
    public function testPurchaseUnauthenticated()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseUnauthenticated.txt');

        new Response(
            $this->getMockRequest(),
            $httpResponse->getBody()
        );
    }
}
