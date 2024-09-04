<?php

namespace OM\Nospam\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected \Magento\Framework\App\ActionFactory $_actionFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected \Magento\Framework\App\ResponseInterface $_response;

    /**
     * @var \OM\Nospam\Api\BlacklistInterface
     */
    protected \OM\Nospam\Api\BlacklistInterface $_blacklist;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected \OM\Nospam\Model\Config $_config;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \OM\Nospam\Api\BlacklistInterface $blacklist
     * @param \OM\Nospam\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \OM\Nospam\Api\BlacklistInterface $blacklist,
        \OM\Nospam\Model\Config $config
    ) {
        $this->_actionFactory = $actionFactory;
        $this->_response = $response;
        $this->_blacklist = $blacklist;
        $this->_config = $config;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return false|\Magento\Framework\App\ActionInterface
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $urlkey = $this->_config->getHoneypotUrlKey();

        if (
            ($identifier == $urlkey) ||
            substr($identifier, 0, strlen($urlkey) + 1) == $urlkey . '/')
        {
            if (!$this->_blacklist->isBlacklisted()) {
                $this->_blacklist->add('badbot');
            }
            exit;
        }
    }
}