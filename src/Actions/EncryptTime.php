<?php

declare(strict_types=1);

namespace OM\Nospam\Actions;

use OM\Nospam\Model\Config;

class EncryptTime
{
    const CIPHER = 'aes-128-cbc';

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @param \OM\Nospam\Model\Config $config
     */
    public function __construct(
        Config $config
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
                $result = openssl_encrypt((string) $time->getTimestamp(), self::CIPHER, $passphrase, 0, $iv);
            }
        }

        return $result;
    }
}