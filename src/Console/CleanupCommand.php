<?php
declare(strict_types=1);

namespace OM\Nospam\Console;

use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use OM\Nospam\Model\Config;
use OM\Nospam\Service\LogService;

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
     * @var \OM\Nospam\Service\LogService
     */
    protected LogService $_logService;

    /**
     * @param \Magento\Framework\App\State $state
     * @param \OM\Nospam\Model\Config $config
     * @param \OM\Nospam\Service\LogService $logService
     */
    public function __construct(
        State $state,
        Config $config,
        LogService $logService
    ) {
        $this->_state = $state;
        $this->_config = $config;
        $this->_logService = $logService;
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
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->_state->setAreaCode(Area::AREA_ADMINHTML);

            if ($this->_config->isModuleEnabled()) {
                $this->_logService->cleanup();
            } else {
                $output->writeln('Module is disabled');
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

        return 1;
    }
}