<?php

namespace Smsc\Exception;

class ReceiversListCouldNotBeEmpty extends \Exception
{
    public function __construct()
    {
        parent::__construct("Receivers list could not be empty");
    }
}