<?php

namespace OM\Nospam\Actions;

class DecryptTime
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
     * @param string $hash
     *
     * @return false|string
     */
    public function execute(string $hash)
    {
        $iv = $this->_config->getTimestampIV();
        $passphrase = $this->_config->getTimestampPassphrase();
        return openssl_decrypt($hash, self::CIPHER, $passphrase, 0, $iv);
    }
}