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

        $receivers = (array)$this->getReceivers();

        if (!count($receivers)) {
            throw new Exception\ReceiversListCouldNotBeEmpty();
        }

        $this->_lastError = [];

        $messageParts = [];

        while (strlen($message) > 760) {
            $index = strpos($message, ' ', 760);
            $messageParts[] = trim(substr($message, 0, $index));
            $message = substr($message, $index);
        }
        $messageParts[] = trim($message);

        $message = $messageParts;

        $errors = [];

        foreach ($message as $index => $messagePart) {

            if (!$this->_send($receivers, $messagePart)) {

                $errors[$index] = $this->getLastError();
            }

            sleep(60);
        }

        $this->_lastError = $errors;

        if (count($this->getLastError())) {
            return false;
        }

        return true;
    }

    /**
     * @param array $receivers
     * @param string $message
     *
     * @return bool
     */
    private function _send($receivers, $message)
    {
        $this->_lastError = null;

        $params = [
            'sender'  => self::getConfig()['sender'],
            'login'   => self::getConfig()['login'],
            'psw'     => self::getConfig()['password'],
            'phones'  => implode(';', $receivers),
            'charset' => 'utf-8',
            'mes'     => urlencode($message),
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
            $this->_lastError = $response['error'];
            return false;
        }

        return true;
    }
}