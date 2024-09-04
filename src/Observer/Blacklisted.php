<?php

namespace OM\Nospam\Observer;

class Blacklisted implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected \Magento\Framework\App\ResponseInterface $_response;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected \Magento\Framework\App\ActionFlag $_actionFlag;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected \Magento\Framework\UrlInterface $_url;

    /**
     * @var \OM\Nospam\Api\BlacklistInterface
     */
    protected \OM\Nospam\Api\BlacklistInterface $_blacklist;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected \OM\Nospam\Model\Config $_config;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected \Magento\Framework\Message\ManagerInterface $_messageManager;

    /**
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\UrlInterface $url
     * @param \OM\Nospam\Api\BlacklistInterface $blacklist
     * @param \OM\Nospam\Model\Config $config
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\UrlInterface $url,
        \OM\Nospam\Api\BlacklistInterface $blacklist,
        \OM\Nospam\Model\Config $config,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_response = $response;
        $this->_actionFlag = $actionFlag;
        $this->_url = $url;
        $this->_blacklist = $blacklist;
        $this->_config = $config;
        $this->_messageManager = $messageManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_config->isModuleEnabled()) {
            return;
        }

        if ($this->_blacklist->isBlacklisted()) {
            $this->_messageManager->addErrorMessage(__('You have been blacklisted.'));
            $url = $this->_config->getNoRouteUrl();
            $redirectUrl = '/';

            if ($url) {
                $redirectUrl = $this->_url->getUrl($url);
            }

            $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            $this->_response->setRedirect($redirectUrl, 404)->sendResponse();
        }
    }
}