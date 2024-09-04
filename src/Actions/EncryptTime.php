<?php

namespace OM\Nospam\Actions;

class EncryptTime
{
    const CIPHER = 'aes-128-cbc';

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected \OM\Nospam\Model\Config $_config;

    /**
     * @param \OM\Nospam\Model\Config $config
     */
    public function __construct(
        \OM\Nospam\Model\Config $config
    ) {
        $this->_config = $config;
    }

    /**
     * @return false|string
     */
    public function execute()
    {
        $result = false;
        $iv = $this->_config->getTimestampIV();
        $passphrase = $this->_config->getTimestampPassphrase();

        if (!empty($iv) && !empty($passphrase)) {
            if (in_array(self::CIPHER, openssl_get_cipher_methods())) {
                $time = new \DateTime();
                $result = openssl_encrypt($time->getTimestamp(), self::CIPHER, $passphrase, 0, $iv);
            }
        }

        return $result;
    }
}