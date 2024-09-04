<?php
namespace OM\Nospam\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected \Magento\Framework\App\State $_state;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected \OM\Nospam\Model\Config $_config;

    /**
     * @var \OM\Nospam\Api\Blacklist
     */
    protected \OM\Nospam\Api\Blacklist $_blacklist;

    /**
     * @param \Magento\Framework\App\State $state
     * @param \OM\Nospam\Model\Config $config
     * @param \OM\Nospam\Api\Blacklist $blacklist
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \OM\Nospam\Model\Config $config,
        \OM\Nospam\Api\Blacklist $blacklist
    ) {
        $this->_state = $state;
        $this->_config = $config;
        $this->_blacklist = $blacklist;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('om:nospam:cleanup')
            ->setDescription('Cleanup blacklist')
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
            $this->_blacklist->cleanup();
        } else {
            $output->writeln('Module is disabled');
        }
    }
}