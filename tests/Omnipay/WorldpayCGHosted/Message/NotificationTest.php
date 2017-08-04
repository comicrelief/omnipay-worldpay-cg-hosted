<?php

namespace Omnipay\WorldpayCGHosted\Message;

use Omnipay\Tests\TestCase;

class NotificationTest extends TestCase
{
    const ORIGIN_IP_VALID   = '195.35.90.1';
    const ORIGIN_IP_BAD     = '10.0.0.99';

    public function testAuthorisedValid()
    {
        $http = $this->getMockHttpResponse('NotificationAuthorised.txt');

        $notification = new Notification(
            $http->getBody(),
            self::ORIGIN_IP_VALID
        );
        $notification->getData();

        $this->assertTrue($notification->isValid());
        $this->assertTrue($notification->isAuthorised());
        $this->assertEquals('AUTHORISED', $notification->getStatus());
        $this->assertEquals('11001100-0000-0000-0000-000011110101', $notification->getTransactionReference());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    public function testAuthorisedFromBadIp()
    {
        $http = $this->getMockHttpResponse('NotificationAuthorised.txt');

        $notification = new Notification(
            $http->getBody(),
            self::ORIGIN_IP_BAD
        );
        $notification->getData();

        $this->assertFalse($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertEquals('AUTHORISED', $notification->getStatus());
        $this->assertEquals('11001100-0000-0000-0000-000011110101', $notification->getTransactionReference());

        $this->assertEquals('[ERROR]', $notification->getResponseBody());
        $this->assertEquals(500, $notification->getResponseStatusCode());
    }

    public function testCaptured()
    {
        $http = $this->getMockHttpResponse('NotificationCaptured.txt');

        $notification = new Notification(
            $http->getBody(),
            self::ORIGIN_IP_VALID
        );
        $notification->getData();

        $this->assertTrue($notification->isValid());
        $this->assertTrue($notification->isAuthorised());
        $this->assertEquals('CAPTURED', $notification->getStatus());
        $this->assertEquals('ExampleOrder1', $notification->getTransactionReference());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    public function testRefused()
    {
        $http = $this->getMockHttpResponse('NotificationRefused.txt');

        $notification = new Notification(
            $http->getBody(),
            self::ORIGIN_IP_VALID
        );
        $notification->getData();

        $this->assertTrue($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertEquals('REFUSED', $notification->getStatus());
        $this->assertEquals('ExampleOrder1', $notification->getTransactionReference());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    public function testCancelled()
    {
        $http = $this->getMockHttpResponse('NotificationCancelled.txt');

        $notification = new Notification(
            $http->getBody(),
            self::ORIGIN_IP_VALID
        );
        $notification->getData();

        $this->assertTrue($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertEquals('CANCELLED', $notification->getStatus());
        $this->assertEquals('ExampleOrder1', $notification->getTransactionReference());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    public function testRefundRequest()
    {
        $http = $this->getMockHttpResponse('NotificationRefundRequest.txt');

        $notification = new Notification(
            $http->getBody(),
            self::ORIGIN_IP_VALID
        );
        $notification->getData();

        $this->assertTrue($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertEquals('SENT_FOR_REFUND', $notification->getStatus());
        $this->assertEquals('ExampleOrder1', $notification->getTransactionReference());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     * @expectedExceptionMessage Non-XML notification body received
     */
    public function testNonXmlResponse()
    {
        $http = $this->getMockHttpResponse('NotificationNonXml.txt');
        new Notification(
            $http->getBody(),
            self::ORIGIN_IP_VALID
        );
    }

    public function testUnexpectedXmlBody()
    {
        $http = $this->getMockHttpResponse('NotificationUnexpectedXml.txt');
        $notification = new Notification(
            $http->getBody(),
            self::ORIGIN_IP_VALID
        );
        $notification->getData();

        $this->assertFalse($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertNull($notification->getStatus());
        $this->assertEquals('ExampleOrder1', $notification->getTransactionReference());

        $this->assertEquals('[ERROR]', $notification->getResponseBody());
        $this->assertEquals(500, $notification->getResponseStatusCode());
    }
}
