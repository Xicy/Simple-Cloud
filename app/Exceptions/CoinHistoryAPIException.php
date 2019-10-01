<?php

namespace App\Exceptions;

use Exception;

class CoinHistoryAPIException extends Exception
{
    protected $header;
    protected $data;

    public function __construct($header, $message = "", $data = null)
    {
        $this->header = $header;
        $this->data = $data;
        parent::__construct($message);
    }
}
