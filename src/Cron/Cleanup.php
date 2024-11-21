<?php
declare(strict_types=1);

namespace OM\Nospam\Cron;

use OM\Nospam\Model\Config;
use OM\Nospam\Service\LogService;

class Cleanup
{
    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @var \OM\Nospam\Service\LogService
     */
    protected LogService $_logService;

    /**
     * @param \OM\Nospam\Model\Config $config
     * @param \OM\Nospam\Service\LogService $logService
     */
    public function __construct(
        Config  $config,
        LogService $logService
    ) {
        $this->_config = $config;
        $this->_logService = $logService;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if ($this->_config->isModuleEnabled()) {
            $this->_logService->cleanup();
        }
    }
}
