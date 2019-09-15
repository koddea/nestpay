<?php

namespace Omnipay\Nestpay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Exception\InvalidResponseException;

class TransactionResponse extends AbstractResponse
{

    private $requestBody = null;
    private $responseBody = null;

    public function __construct(RequestInterface $request, $requestXml, $responseXml) {

        $this->requestBody = $requestXml;
        $this->responseBody = $responseXml;
        $this->request = $request;
        try {
            $this->data = (array) simplexml_load_string($responseXml);
        } catch (\Exception $ex) {
            throw new InvalidResponseException();
        }
    }

    public function getRequestBody()
    {
        return $this->requestBody;
    }

    public function getResponseBody()
    {
        return $this->responseBody;
    }

    public function isSuccessful() {
        if (isset($this->data["ProcReturnCode"])) {
            return (string) $this->data["ProcReturnCode"] === '00' || $this->data["Response"] === 'Approved';
        }
        return false;
    }

    public function getMessage()
    {
        return $this->data['ErrMsg'];
    }

    public function getTransactionReference()
    {
        return $this->isSuccessful() ? $this->data["TransId"] : '';
    }

    public function getCode()
    {
        return $this->isSuccessful() ? $this->data["AuthCode"] : parent::getCode();
    }

}
