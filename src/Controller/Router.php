<?php
declare(strict_types=1);

namespace OM\Nospam\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use OM\Nospam\Api\BlacklistInterface;
use OM\Nospam\Model\Config;

class Router implements RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected ActionFactory $_actionFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected ResponseInterface $_response;

    /**
     * @var \OM\Nospam\Api\BlacklistInterface
     */
    protected BlacklistInterface $_blacklist;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \OM\Nospam\Api\BlacklistInterface $blacklist
     * @param \OM\Nospam\Model\Config $config
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        BlacklistInterface $blacklist,
        Config $config
    ) {
        $this->_actionFactory = $actionFactory;
        $this->_response = $response;
        $this->_blacklist = $blacklist;
        $this->_config = $config;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    public function match(RequestInterface $request)
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