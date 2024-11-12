<?php
declare(strict_types=1);

namespace OM\Nospam\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\ManagerInterface;
use OM\Nospam\Api\LogInterface;
use OM\Nospam\Model\Config;

class Blacklisted implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected ResponseInterface $_response;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected ActionFlag $_actionFlag;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected UrlInterface $_url;

    /**
     * @var \OM\Nospam\Api\LogInterface
     */
    protected LogInterface $_log;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected ManagerInterface $_messageManager;

    /**
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\UrlInterface $url
     * @param \OM\Nospam\Api\LogInterface $log
     * @param \OM\Nospam\Model\Config $config
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        ResponseInterface $response,
        ActionFlag $actionFlag,
        UrlInterface $url,
        LogInterface $log,
        Config $config,
        ManagerInterface $messageManager
    ) {
        $this->_response = $response;
        $this->_actionFlag = $actionFlag;
        $this->_url = $url;
        $this->_log = $log;
        $this->_config = $config;
        $this->_messageManager = $messageManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_config->isModuleEnabled()) {
            return;
        }

        if ($this->_log->isBlacklisted()) {
            $this->_messageManager->addErrorMessage(__(LogInterface::ERROR_MSG_BLACKLISTED));
            $url = $this->_config->getNoRouteUrl();
            $redirectUrl = '/';

            if ($url) {
                $redirectUrl = $this->_url->getUrl($url);
            }

            $this->_actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $this->_response->setRedirect($redirectUrl, 404)->sendResponse();
        }
    }
}