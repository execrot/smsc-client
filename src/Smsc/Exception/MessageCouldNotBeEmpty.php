<?php

namespace Smsc\Exception;

class MessageCouldNotBeEmpty extends \Exception
{
    public function __construct()
    {
        parent::__construct("Message could not be empty");
    }
}