<?php
declare(strict_types=1);

namespace OM\Nospam\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Action\Action;
use Psr\Log\LoggerInterface;
use OM\Nospam\Service\LogService;
use OM\Nospam\Service\DomainService;
use OM\Nospam\Model\Config;
use OM\Nospam\Actions\DecryptTime;

class ControllerActionPredispatch implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected RequestInterface $_request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected ResponseInterface $_response;

    /**
     * @var \Magento\Framework\UrlInterface 
     */
    protected UrlInterface $_url;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected ActionFlag $_actionFlag;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected RedirectInterface $_redirect;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected RedirectFactory $_redirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected ManagerInterface $_messageManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $_logger;

    /**
     * @var \OM\Nospam\Service\LogService
     */
    protected LogService $_logService;

    /**
     * @var \OM\Nospam\Service\DomainService
     */
    protected DomainService $_domainService;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @var \OM\Nospam\Actions\DecryptTime
     */
    protected DecryptTime $_decryptTime;

    /**
     * @var array
     */
    protected array $_flatPost = [];

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \OM\Nospam\Service\LogService $logService
     * @param \OM\Nospam\Service\DomainService $domainService
     * @param \OM\Nospam\Model\Config $config
     * @param \OM\Nospam\Actions\DecryptTime $decryptTime
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ActionFlag $actionFlag,
        UrlInterface $url,
        RedirectInterface $redirect,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        LogService $logService,
        DomainService $domainService,
        Config $config,
        DecryptTime $decryptTime
    ) {
        $this->_request = $request;
        $this->_response = $response;
        $this->_actionFlag = $actionFlag;
        $this->_url = $url;
        $this->_redirect = $redirect;
        $this->_redirectFactory = $redirectFactory;
        $this->_messageManager = $messageManager;
        $this->_logger = $logger;
        $this->_logService = $logService;
        $this->_domainService = $domainService;
        $this->_config = $config;
        $this->_decryptTime = $decryptTime;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->_config->isModuleEnabled()) {
            return;
        }

        /**
         * Timestamps
         */
        if ($this->_config->useFormTimestamps()) {
            if (!$this->_checkFormTimestamp()) {
                $this->_redirect($this->_getDenyUrl());
                return;
            }
        }

        /**
         * Honeypots
         */
        if ($this->_config->useFormHoneypots()) {
            if (!$this->_checkFormHoneypot()) {
                $this->_redirect($this->_getDenyUrl());
                return;
            }
        }

        /**
         * Regex
         */
        if ($this->_config->useFormRegex()) {
            if (!$this->_checkRegex()) {
                $this->_redirect($this->_getDenyUrl());
                return;
            }
        }

        /**
         * Blacklisted mail domain
         */
        if ($this->_config->checkBlacklistedMailDomains()) {
            if ($this->_isEmailDomainBlacklisted()) {
                $this->_messageManager->addErrorMessage(__('This email domain is not allowed. Please pick another email address.'));
                $this->_redirect($this->_redirect->getRefererUrl(), 400);
                return;
            }
        }
    }

    /**
     * @return string
     */
    protected function _getUri(): string
    {
        return str_replace($this->_url->getBaseUrl(), '', $this->_url->getCurrentUrl());
    }

    /**
     * @return bool
     */
    protected function _shouldCheckPostDataTimestamp(): bool
    {
        $result = false;
        $post = $this->_getFlatPostData();
        $actions = $this->_config->getFormTimestampActions();

        if (is_array($actions)) {
            $url = $this->_url->getCurrentUrl();

            foreach ($actions as $action) {
                $action = trim($action, '/');
                if (stripos($url, $action) !== null) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function _isEmailDomainBlacklisted(): bool
    {
        $result = false;
        $post = $this->_getFlatPostData();
        $email = $post['email'] ?? false;

        if ($email && $this->_domainService->isBlacklisted($email)) {
            $result = true;
            $this->_logService->add('Blacklisted mail domain: ' . $email);
        }

        return $result;
    }

    /**
     * @return bool
     * @see \ViewModel\Template::getFormTimestamp()
     */
    protected function _checkFormTimestamp(): bool
    {
        $result = true;

        if ($this->_shouldCheckPostDataTimestamp()) {
            $post = $this->_getFlatPostData();
            $actions = $this->_config->getFormTimestampActions();
            $timestamp = $post[$this->_config->getTimestampFieldName()] ?? false;
            $threshold = $this->_config->getTimestampThreshold();

            if (!empty($timestamp) && !empty($threshold)) {
                $current = time();
                $timestamp = $this->_decryptTime->execute($timestamp);

                if (($current - $timestamp) <= $threshold) {
                    $this->_logService->add('Timestamp in ' . $this->_getUri());
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function _checkFormHoneypot(): bool
    {
        $result = true;
        $actions = $this->_config->getFormHoneypotActions();

        if (!empty($actions)) {
            $post = $this->_getFlatPostData();
            $uri = $this->_getUri();

            foreach ($actions as $action) {
                if (stripos($uri, trim($action['action'], '/')) !== false) {
                    $name = str_replace(' ', '-', strtolower($action['name']));

                    if (!isset($post[$name]) || !empty($post[$name])) {
                        $this->_logService->add("Honeypot: '$name' in URI: $uri");
                        $result = false;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Checks all available post fields against all defined regular expressions.
     * If any expression matches the user ip will be added to the log
     * and a redirect URL is set.
     *
     * @return bool
     */
    protected function _checkRegex(): bool
    {
        $result = true;

        $post = $this->_getFlatPostData();
        $regex = $this->_config->getRegex();
        $exclude = $this->_config->getExcludeFromRegex();

        foreach ($post as $key => $field) {
            if (empty($field) || in_array($key, $exclude)) {
                continue;
            }

            foreach ($regex as $name => $expression) {
                try {
                    if (preg_match($expression, $field)) {
                        $this->_logService->add('Regex: ' . $name . ' (' . $expression . ') in "' . $key . '": "' . $field . '"');
                        $result = false;
                        break 2;
                    }
                } catch (\Exception $e) {
                    $this->_logger->critical($e->getMessage());
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function _getFlatPostData(): array
    {
        if (empty($this->_flatPost)) {
            $this->_flatPost = $this->_flattenArray($this->_request->getParams());
        }

        return $this->_flatPost;
    }

    /**
     * @param $array
     *
     * @return array
     */
    protected function _flattenArray($array): array
    {
        $result = [];
        foreach ($array as $key => $element) {
            if (is_array($element)) {
                $result = array_merge($result, $this->_flattenArray($element));
            } else {
                $result[$key] = $element;
            }
        }
        return $result;
    }

    /**
     * @return void
     */
    protected function _redirect($url, $code = 404)
    {
        $this->_actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
        $this->_response->setRedirect($url, $code)->sendResponse();
    }

    /**
     * @return string
     */
    protected function _getDenyUrl(): string
    {
        $url = $this->_config->getNoRouteUrl();
        $redirectUrl = '/';

        if ($url) {
            $redirectUrl = $this->_url->getUrl($url);
        }

        return $redirectUrl;
    }
}