<?php
declare(strict_types=1);

namespace OM\Nospam\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use OM\Nospam\Service\LogService;
use OM\Nospam\Model\Config;

class Router implements RouterInterface
{
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected ResponseInterface $_response;

    /**
     * @var \OM\Nospam\Service\LogService
     */
    protected LogService $_logService;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \OM\Nospam\Service\LogService $logService
     * @param \OM\Nospam\Model\Config $config
     */
    public function __construct(
        ResponseInterface $response,
        LogService $logService,
        Config $config
    ) {
        $this->_response = $response;
        $this->_logService = $logService;
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
            if (!$this->_logService->isBlacklisted()) {
                $this->_logService->add('badbot');
            }
        }
    }
}