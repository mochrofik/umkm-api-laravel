<?php

namespace App\Exceptions;

use Exception;

class UserNotRegisteredException extends Exception
{
    protected $data;

    public function __construct($message = 'User not registered', $data = null)
    {
        parent::__construct($message, 401);
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
