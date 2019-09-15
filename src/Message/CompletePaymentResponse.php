<?php

namespace Omnipay\Nestpay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Exception\InvalidResponseException;

class CompletePaymentResponse extends AbstractResponse
{

    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $this->data = $data;
        if (!$this->signHash()) {
            throw new InvalidResponseException('Sayısal İmza Doğrulanmadı');
        }
    }

    public function isSuccessful()
    {
        if($this->request->getStoreType = "3d_pay_hosting"){
            return $this->data["Response"] === 'Approved';
        }
        return in_array($this->data['mdStatus'], [1, 2, 3, 4]) && $this->data["Response"] === 'Approved';
    }

    public function getMessage()
    {
        return isset($this->data['ErrMsg']) ? $this->data['ErrMsg'] : $this->data['mdErrorMsg'];
    }

    public function getTransactionReference()
    {
        return $this->isSuccessful() ? $this->data["TransId"] : '';
    }

    public function getAmount()
    {
        return $this->data['amount'];
    }

    public function getCurrency()
    {
        return $this->data['currency'];
    }

    public function getOrderId()
    {
        return $this->data['ReturnOid'];
    }

    public function getCode()
    {
        return $this->isSuccessful() ? $this->data["AuthCode"] : parent::getCode();
    }

    private function signHash()
    {
        $hashParams = explode(':', $this->data['HASHPARAMS']);
        $signature = "";
        foreach ($hashParams as $parameter) {
            if (isset($this->data[$parameter])) {
                $signature .= $this->data[$parameter];
            }
        }
        $generateHash = base64_encode(pack('H*', sha1($signature . $this->request->getStoreKey())));
        if ($signature != $this->data["HASHPARAMSVAL"] || $generateHash != $this->data["HASH"]) {
            return false;
        }
        return true;
    }
}
