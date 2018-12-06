<?php
namespace Omnipay\Erede\Message;

class FetchTransactionRequest extends AbstractRequest
{

    public function sendData($data)
    {
        $headers = $this->getDefaultHeaders();

        $endPoint = $this->endpoint . '/transactions';
        if (!empty($data['reference'])) {
            $endPoint .= '?reference=' . $data['reference'];
        } else {
            $endPoint .= '/' . $data['transactionId'];
        }

        try {
            $get = $this->httpClient->request(
                'GET',
                $endPoint,
                $headers
            );
        } catch (\Exception $ex) {

        }

        $response = json_decode(strval($get->getBody()->getContents()), true);

        return new Response($this, $response);
    }

    public function getData()
    {
        $data = array_merge(parent::getData(), $this->getParameters());


        if (isset($data['reference'])) {
            return [
                'reference' => $data['reference']
            ];
        }

        if (isset($data['transactionId'])) {
            return [
                'transactionId' => $data['transactionId']
            ];
        }

        throw new \InvalidArgumentException('"transactionId" or "reference" must be provided.');
    }

    public function getReference()
    {
        return $this->getParameter('reference');
    }

    public function setReference($reference)
    {
        return $this->setParameter('reference', $reference);
    }
}
