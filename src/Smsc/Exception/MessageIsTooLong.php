<?php

namespace Smsc\Exception;

class MessageIsTooLong extends \Exception
{
    public function __construct()
    {
        parent::__construct("Message is too long");
    }
}