<?php
namespace Omnipay\Erede\Message;

use Omnipay\Rede\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2017-11-07 at 19:08:31.
 */
class CaptureRequestTest extends TestCase
{

    public function testSuccessfulCapture()
    {
        $gateway  = $this->getGateway();
        $object   = $gateway->authorize();
        $this->populateValidRequest($object);

        $response = $object->send();
        $this->assertTrue($response->isSuccessful());
        $transactionId = $response->getTransactionId();

        $captureResponse = $gateway->capture([
            'transactionId' => $transactionId,
            'amount'        => $object->getAmount()
        ])->send();

        $this->assertTrue($captureResponse->isSuccessful());
    }

    public function testFailedCapture()
    {
        $gateway  = $this->getGateway();
        $response = $gateway->capture([
            'transactionId' => uniqid(),
            'amount'        => 100.00
        ])->send();

        $this->assertFalse($response->isSuccessful());
    }

}
