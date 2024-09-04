<?php

namespace OM\Nospam\Cron;

class Cleanup
{
    /**
     * @var \OM\Nospam\Model\Config
     */
    protected \OM\Nospam\Model\Config $_config;

    /**
     * @var \OM\Nospam\Api\Blacklist
     */
    protected \OM\Nospam\Api\Blacklist $_blacklist;

    /**
     * @param \OM\Nospam\Model\Config $config
     * @param \OM\Nospam\Api\Blacklist $blacklist
     */
    public function __construct(
        \OM\Nospam\Model\Config $config,
        \OM\Nospam\Api\Blacklist $blacklist
    ) {
        $this->_config = $config;
        $this->_blacklist = $blacklist;
    }

    /**
     *
     */
    public function execute()
    {
        if ($this->_config->isModuleEnabled()) {
            $this->_blacklist->cleanup();
        }
    }
}
