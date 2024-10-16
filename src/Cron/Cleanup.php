<?php
declare(strict_types=1);

namespace OM\Nospam\Cron;

use OM\Nospam\Model\Config;
use OM\Nospam\Api\Blacklist;

class Cleanup
{
    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @var \OM\Nospam\Api\Blacklist
     */
    protected Blacklist $_blacklist;

    /**
     * @param \OM\Nospam\Model\Config $config
     * @param \OM\Nospam\Api\Blacklist $blacklist
     */
    public function __construct(
        Config  $config,
        Blacklist $blacklist
    ) {
        $this->_config = $config;
        $this->_blacklist = $blacklist;
    }

    /**
     *
     */
    public function execute(): void
    {
        if ($this->_config->isModuleEnabled()) {
            $this->_blacklist->cleanup();
        }
    }
}
