<?php
namespace Omnipay\Erede\Message;

class RefundRequest extends AbstractRequest
{

    public function sendData($data)
    {
        $headers = $this->getDefaultHeaders();

        try {

            $put = $this->httpClient->request('PUT',
                $this->endpoint . '/transactions/' . $this->getTransactionId() . '/refunds',
                $headers,
                json_encode($data)
            );

        } catch (\Exception $ex) {}

        $response = json_decode(strval($put->getResponse()->getBody()), true);

        return new Response($this, $response, ['00', '359', '360']);
    }

    public function getData()
    {
        $this->validate('transactionId', 'amount');

        return [
            'amount' => $this->getAmountInteger()
        ];
    }

}

