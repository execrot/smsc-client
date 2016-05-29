<?php

namespace Smsc;

use Smsc\Exception;

class Smsc
{
    /**
     * @var array
     */
    private static $_config = null;

    /**
     * @return array
     */
    public static function getConfig()
    {
        return self::$_config;
    }

    /**
     * @param array $config
     */
    public static function setConfig($config)
    {
        self::$_config = $config;
    }

    /**
     * @var array
     */
    protected $_receivers = [];

    /**
     * @var string
     */
    protected $_message =  null;

    /**
     * @var string
     */
    protected $_lastError = null;

    /**
     * @throws Exception\CredentialsWasNotProvided
     */
    public function __construct()
    {
        if (!self::getConfig()) {
            throw new Exception\CredentialsWasNotProvided();
        }
    }

    /**
     * @return array
     */
    public function getReceivers()
    {
        return $this->_receivers;
    }

    /**
     * @param array $receivers
     *
     * @return Smsc
     */
    public function setReceivers(array $receivers)
    {
        $this->_receivers = $receivers;
        return $this;
    }

    /**
     * @param string $receiver
     * @return Smsc
     */
    public function addReceiver($receiver)
    {
        $this->_receivers = (array)$this->_receivers;
        $this->_receivers[] = $receiver;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * @param string $message
     * @return Smsc
     */
    public function setMessage($message)
    {
        $this->_message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastError()
    {
        return $this->_lastError;
    }

    /**
     * @return bool
     *
     * @throws Exception\MessageCouldNotBeEmpty
     * @throws Exception\MessageIsTooLong
     * @throws Exception\ReceiversListCouldNotBeEmpty
     */
    public function send()
    {
        $message = $this->getMessage();

        if (!mb_strlen($message)) {
            throw new Exception\MessageCouldNotBeEmpty();
        }

//        if (mb_strlen($message) > 500) {
//            throw new Exception\MessageIsTooLong();
//        }

        $receivers = (array)$this->getReceivers();

        if (!count($receivers)) {
            throw new Exception\ReceiversListCouldNotBeEmpty();
        }

        $this->_lastError = [];

        foreach (str_split($message, 750) as $index => $messagePart) {

            $params = [
                'sender'  => self::getConfig()['sender'],
                'login'   => self::getConfig()['login'],
                'psw'     => self::getConfig()['password'],
                'phones'  => implode(';', $receivers),
                'charset' => 'utf-8',
                'mes'     => urlencode($messagePart),
                'fmt'     => '3'
            ];

            $url = implode('?', [
                self::getConfig()['uri'],
                implode('&', array_map(function ($key, $value) {
                    return "{$key}={$value}";
                }, array_keys($params), array_values($params)))
            ]);

            $response = json_decode(file_get_contents($url), true);

            if (!empty($response['error'])) {
                $this->_lastError[$index] = $response['error'];
            }
           sleep(2);
        }

        if (count($this->getLastError())) {
            return false;
        }

        return true;
    }
}
