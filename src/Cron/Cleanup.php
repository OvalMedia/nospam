<?php
declare(strict_types=1);

namespace OM\Nospam\Cron;

use OM\Nospam\Model\Config;
use OM\Nospam\Api\Log;

class Cleanup
{
    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @var \OM\Nospam\Api\Log
     */
    protected Log $_log;

    /**
     * @param \OM\Nospam\Model\Config $config
     * @param \OM\Nospam\Api\Log $log
     */
    public function __construct(
        Config  $config,
        Log $log
    ) {
        $this->_config = $config;
        $this->_log = $log;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if ($this->_config->isModuleEnabled()) {
            $this->_log->cleanup();
        }
    }
}
