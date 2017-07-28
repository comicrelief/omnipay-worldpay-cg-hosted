<?php

namespace Omnipay\WorldpayCGHosted\Message;

use Omnipay\Tests\TestCase;

class RedirectResponseTest extends TestCase
{
    public function testUrlConstruction()
    {
        $httpResponse = $this->getMockHttpResponse('InitSuccessRedirect.txt');
        $response = new RedirectResponse(
            $this->getMockRequest(),
            $httpResponse->getBody()
        );
        $response->setSuccessUrl('https://www.example.com/success');
        $response->setFailureUrl('https://www.example.com/failure');
        $response->setCancelUrl('https://www.example.com/cancel');

        $expected = <<<EOT
https://payments-test.worldpay.com/app/hpp/integration/wpg/corporate?OrderKey=OTNGBP%5E11001100-0000-0000-0000-000011110101&Ticket=999988889999888899AaaaA9AAAA8aA9AAaaaaA&successURL=https%3A%2F%2Fwww.example.com%2Fsuccess&pendingURL=https%3A%2F%2Fwww.example.com%2Fsuccess&failureURL=https%3A%2F%2Fwww.example.com%2Ffailure&errorURL=https%3A%2F%2Fwww.example.com%2Ffailure&cancelURL=https%3A%2F%2Fwww.example.com%2Fcancel
EOT;

        $this->assertEquals($expected, $response->getRedirectUrl());
    }
}