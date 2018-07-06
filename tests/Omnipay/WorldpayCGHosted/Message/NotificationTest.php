<?php

namespace Omnipay\WorldpayCGHosted\Tests\Message;

use Omnipay\Tests\TestCase;
use Omnipay\WorldpayCGHosted\Message\Notification;

class NotificationTest extends TestCase
{
    const ORIGIN_IP_VALID   = '195.35.90.1';
    const ORIGIN_IP_BAD     = '10.0.0.99';

    public function testAuthorisedValid()
    {
        $http = $this->getMockHttpResponse('NotificationAuthorised.txt');

        $notification = new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_VALID
        );

        $this->assertTrue($notification->isValid());
        $this->assertTrue($notification->isAuthorised());
        $this->assertFalse($notification->isPending());
        $this->assertFalse($notification->isCancelled());
        $this->assertEquals('AUTHORISED', $notification->getStatus());
        $this->assertInternalType('string', $notification->getTransactionId());
        $this->assertEquals('11001100-0000-0000-0000-000011110101', $notification->getTransactionId());
        $this->assertEquals('ECMC-SSL', $notification->getCardType());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    public function testSentForAuthorisationValid()
    {
        $http = $this->getMockHttpResponse('NotificationSentForAuth.txt');

        $notification = new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_VALID
        );

        $this->assertTrue($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertTrue($notification->isPending());
        $this->assertFalse($notification->isCancelled());
        $this->assertEquals('SENT_FOR_AUTHORISATION', $notification->getStatus());
        $this->assertEquals('11001100-0000-0000-0000-000011110101', $notification->getTransactionId());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    public function testAuthorisedFromBadIp()
    {
        $http = $this->getMockHttpResponse('NotificationAuthorised.txt');

        $notification = new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_BAD
        );

        $this->assertFalse($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertFalse($notification->isPending());
        $this->assertFalse($notification->isCancelled());
        $this->assertEquals('AUTHORISED', $notification->getStatus());
        $this->assertEquals('11001100-0000-0000-0000-000011110101', $notification->getTransactionId());

        $this->assertEquals('[ERROR]', $notification->getResponseBody());
        $this->assertEquals(500, $notification->getResponseStatusCode());
    }

    public function testAuthorisedFromInvalidIp()
    {
        $http = $this->getMockHttpResponse('NotificationAuthorised.txt');

        $notification = new Notification(
            $http->getBody()->__toString(),
            'not-a-real-ip'
        );

        $this->assertFalse($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertFalse($notification->isPending());
        $this->assertFalse($notification->isCancelled());
        $this->assertEquals('AUTHORISED', $notification->getStatus());
        $this->assertEquals('11001100-0000-0000-0000-000011110101', $notification->getTransactionId());

        $this->assertEquals('[ERROR]', $notification->getResponseBody());
        $this->assertEquals(500, $notification->getResponseStatusCode());
    }

    public function testAuthorisedFromMissingIp()
    {
        $http = $this->getMockHttpResponse('NotificationAuthorised.txt');

        $notification = new Notification(
            $http->getBody()->__toString(),
            '' // no origin IP
        );

        $this->assertFalse($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertFalse($notification->isPending());
        $this->assertFalse($notification->isCancelled());
        $this->assertEquals('AUTHORISED', $notification->getStatus());
        $this->assertEquals('11001100-0000-0000-0000-000011110101', $notification->getTransactionId());

        $this->assertEquals('[ERROR]', $notification->getResponseBody());
        $this->assertEquals(500, $notification->getResponseStatusCode());
    }

    /**
     * Unexpected case, but check we handle the missing reference correctly
     */
    public function testAuthorisedMissingOrderCode()
    {
        $http = $this->getMockHttpResponse('NotificationAuthorisedMissingOrderCode.txt');

        $notification = new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_VALID
        );

        $this->assertTrue($notification->isValid());
        $this->assertTrue($notification->isAuthorised());
        $this->assertFalse($notification->isPending());
        $this->assertFalse($notification->isCancelled());
        $this->assertEquals('AUTHORISED', $notification->getStatus());

        // This should normally lead client apps to refuse the transaction as unexpected, but this logic is up to
        // them - it doesn't make the payment intrinsically invalid.
        $this->assertNull($notification->getTransactionId());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    public function testCaptured()
    {
        $http = $this->getMockHttpResponse('NotificationCaptured.txt');

        $notification = new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_VALID
        );

        $this->assertTrue($notification->isValid());
        $this->assertTrue($notification->isAuthorised());
        $this->assertFalse($notification->isPending());
        $this->assertFalse($notification->isCancelled());
        $this->assertEquals('CAPTURED', $notification->getStatus());
        $this->assertEquals('ExampleOrder1', $notification->getTransactionId());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    public function testRefused()
    {
        $http = $this->getMockHttpResponse('NotificationRefused.txt');

        $notification = new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_VALID
        );

        $this->assertTrue($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertFalse($notification->isPending());
        $this->assertFalse($notification->isCancelled());
        $this->assertEquals('REFUSED', $notification->getStatus());
        $this->assertEquals('ExampleOrder1', $notification->getTransactionId());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    public function testCancelled()
    {
        $http = $this->getMockHttpResponse('NotificationCancelled.txt');

        $notification = new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_VALID
        );

        $this->assertTrue($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertFalse($notification->isPending());
        $this->assertTrue($notification->isCancelled());
        $this->assertEquals('CANCELLED', $notification->getStatus());
        $this->assertEquals('ExampleOrder1', $notification->getTransactionId());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    public function testRefundRequest()
    {
        $http = $this->getMockHttpResponse('NotificationRefundRequest.txt');

        $notification = new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_VALID
        );

        $this->assertTrue($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertFalse($notification->isPending());
        $this->assertFalse($notification->isCancelled());
        $this->assertEquals('SENT_FOR_REFUND', $notification->getStatus());
        $this->assertEquals('ExampleOrder1', $notification->getTransactionId());

        $this->assertEquals('[OK]', $notification->getResponseBody());
        $this->assertEquals(200, $notification->getResponseStatusCode());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage Notification data not loaded as XML
     */
    public function testNonXmlResponse()
    {
        $http = $this->getMockHttpResponse('NotificationNonXml.txt');
        new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_VALID
        );
    }

    public function testUnexpectedXmlBody()
    {
        $http = $this->getMockHttpResponse('NotificationUnexpectedXml.txt');
        $notification = new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_VALID
        );

        $this->assertFalse($notification->isValid());
        $this->assertFalse($notification->isAuthorised());
        $this->assertFalse($notification->isPending());
        $this->assertFalse($notification->isCancelled());
        $this->assertNull($notification->getStatus());
        $this->assertEquals('ExampleOrder1', $notification->getTransactionId());

        $this->assertEquals('[ERROR]', $notification->getResponseBody());
        $this->assertEquals(500, $notification->getResponseStatusCode());
    }

    public function testContainsTokenWithId()
    {
        $http = $this->getMockHttpResponse('NotificationWithToken.txt');
        $notification = new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_VALID
        );

        $this->assertTrue($notification->isValid());
        $this->assertNotNull($notification->getPaymentTokenID());
        $this->assertInstanceOf(\DateTimeInterface::class, $notification->getPaymentTokenExpiry());
    }

    public function testContainsTokenWithoutId()
    {
        $http = $this->getMockHttpResponse('NotificationRefusedWithToken.txt');
        $notification = new Notification(
            $http->getBody()->__toString(),
            self::ORIGIN_IP_VALID
        );

        $this->assertTrue($notification->isValid());
        $this->assertNull($notification->getPaymentTokenID());
        $this->assertFalse($notification->getPaymentTokenExpiry());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage Notification data empty
     */
    public function testEmptyData()
    {
        new Notification('', self::ORIGIN_IP_VALID);
    }
}
