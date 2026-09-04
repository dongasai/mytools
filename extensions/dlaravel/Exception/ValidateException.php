<?php

namespace DLaravel\Exception;

use DLaravel\ValidationCore;
use Throwable;

class ValidateException extends \Exception
{
    /**
     * @var ValidationCore
     */
    public $validation;

    public function __construct($validation, $message = '', $code = 422, ?Throwable $previous = null)
    {
        $this->validation = $validation;

        parent::__construct($message, $code, $previous);
    }
}
