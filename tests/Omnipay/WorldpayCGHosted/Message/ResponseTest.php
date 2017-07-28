<?php

namespace Omnipay\WorldpayCGHosted\Message;

use Omnipay\Tests\TestCase;

class ResponseTest extends TestCase
{
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     */
    public function testConstructEmpty()
    {
        new Response($this->getMockRequest(), '');
    }

    public function testPurchaseSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('InitSuccessRedirect.txt');
        $response = new Response(
            $this->getMockRequest(),
            $httpResponse->getBody()
        );

        $this->assertTrue($response->isRedirect());
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('11001100-0000-0000-0000-000011110101', $response->getTransactionReference());
        $this->assertEquals('PENDING', $response->getMessage());
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
        $this->assertEquals('T0211234', $response->getTransactionReference());
        $this->assertSame('CARD EXPIRED', $response->getMessage());
    }
}
