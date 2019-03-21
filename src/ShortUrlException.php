<?php

namespace Bnb\Linkr;

class ShortUrlException extends \Exception
{

    public function __construct($message)
    {
        parent::__construct('Failed to build short URL: ' . $message, 500);
    }
}