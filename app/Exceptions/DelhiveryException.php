<?php

namespace App\Exceptions;

use Exception;

class DelhiveryException extends Exception
{
    protected array $context = [];

    public function __construct(string $message = 'Delhivery API error', int $code = 0, array $context = [])
    {
        parent::__construct($message, $code);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}