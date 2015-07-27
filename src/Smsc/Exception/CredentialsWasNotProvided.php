<?php

namespace Smsc\Exception;

class CredentialsWasNotProvided extends \Exception
{
    public function __construct()
    {
        parent::__construct("Credentials was not provided");
    }
}