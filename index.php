<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "vendor/autoload.php";

\Smsc\Smsc::setConfig([
    'uri' => 'http://smsc.ru/sys/send.php?',
    'login' => '{login}',
    'password' => '{password}',
    'sender' => '{sender-id}'
]);

$sms = new \Smsc\Smsc();

$sms
    ->addReceiver('{number}')
    ->setMessage('{message}');

if (!$sms->send()) {
    echo $sms->getLastError();
}