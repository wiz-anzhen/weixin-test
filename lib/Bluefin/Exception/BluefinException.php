<?php

namespace Bluefin\Exception;

class BluefinException extends \Exception
{
    public function __construct($message, $code = \Bluefin\Common::HTTP_INTERNAL_SERVER_ERROR, \Exception $previousException = null)
    {
        parent::__construct($message ? $message : \Bluefin\Common::getStatusCodeMessage($code), $code, $previousException);
    }

    public function sendHttpResponse()
    {
        header("HTTP/1.1 " . $this->getCode() . ' ' . \Bluefin\Common::getStatusCodeMessage($this->getCode()));
        header("Content-Type: application/json");
        header("Cache-Control: no-store");
        if (RENDER_EXCEPTION)
        {
            echo json_encode_cn($this->getMessage() . $this->getTraceAsString());
        }
        else
        {
            echo json_encode_cn($this->getMessage());
        }
        exit();
    }
}