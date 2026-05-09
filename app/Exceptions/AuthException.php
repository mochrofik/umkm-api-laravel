<?php

namespace App\Exceptions;

use Exception;

class AuthException extends Exception
{

    protected $data;

    public function __construct(string $message, $data = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

 
}
