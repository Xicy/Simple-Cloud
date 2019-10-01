<?php

namespace App\Exceptions;

use Exception;

class MissingCoinInformationException extends Exception
{
    protected $id;
    protected $key;

    public function __construct($id, $key)
    {
        $this->id = $id;
        $this->key = $key;
        parent::__construct("ID: {$id} Key: {$key}");
    }

}
