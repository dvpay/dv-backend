<?php

namespace App\Exceptions\Processing;

use Exception;

class ResourceException extends Exception
{
    public function __construct()
    {
        parent::__construct("Processing resource not enough");
    }
}
