<?php

namespace App\Exceptions\Processing;

use Exception;

class QueueException extends Exception
{
    public function __construct()
    {
        parent::__construct("Processing Queue is busy");
    }
}
