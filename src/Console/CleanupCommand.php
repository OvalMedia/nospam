<?php
declare(strict_types=1);

namespace OM\Nospam\Console;

use Magento\Framework\App\State;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use OM\Nospam\Model\Config;
use OM\Nospam\Api\Log;

class CleanupCommand extends Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected State $_state;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @var \OM\Nospam\Api\Log
     */
    protected Log $_log;

    /**
     * @param \Magento\Framework\App\State $state
     * @param \OM\Nospam\Model\Config $config
     * @param \OM\Nospam\Api\Log $log
     */
    public function __construct(
        State $state,
        Config $config,
        Log $log
    ) {
        $this->_state = $state;
        $this->_config = $config;
        $this->_log = $log;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('om:nospam:cleanup')
            ->setDescription('Cleanup log')
        ;

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        if ($this->_config->isModuleEnabled()) {
            $this->_log->cleanup();
        } else {
            $output->writeln('Module is disabled');
        }
    }
}