<?php

namespace OM\Nospam\Observer;

class ControllerActionPredispatch implements \Magento\Framework\Event\ObserverInterface
{
    const FLAG_SUSPICIOUS_URL = 'flag_suspicious_url_checked';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected \Magento\Framework\App\RequestInterface $_request;

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
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected \Magento\Framework\App\Response\RedirectInterface $_redirect;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected \Magento\Framework\Controller\Result\RedirectFactory $_redirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected \Magento\Framework\Message\ManagerInterface $_messageManager;

    /**
     * @var \OM\Nospam\Api\BlacklistInterface 
     */
    protected \OM\Nospam\Api\BlacklistInterface $_blacklist;

    /**
     * @var \OM\Nospam\Api\UrlInterface
     */
    protected \OM\Nospam\Api\UrlInterface $_suspiciousUrl;

    /**
     * @var \OM\Nospam\Api\DomainInterface
     */
    protected \OM\Nospam\Api\DomainInterface $_domain;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected \OM\Nospam\Model\Config $_config;

    /**
     * @var \OM\Nospam\Actions\DecryptTime
     */
    protected \OM\Nospam\Actions\DecryptTime $_decryptTime;

    /**
     * @var bool
     */
    protected bool $_suspiciousUrlChecked = false;

    protected $_status;

    protected $_statusCode;

    protected $_redirectUrl;

    protected $_message;

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
     * @param \OM\Nospam\Api\BlacklistInterface $blacklist
     * @param \OM\Nospam\Api\UrlInterface $suspiciousUrl
     * @param \OM\Nospam\Api\DomainInterface $domain
     * @param \OM\Nospam\Model\Config $config
     * @param \OM\Nospam\Actions\DecryptTime $decryptTime
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \OM\Nospam\Api\BlacklistInterface $blacklist,
        \OM\Nospam\Api\UrlInterface $suspiciousUrl,
        \OM\Nospam\Api\DomainInterface $domain,
        \OM\Nospam\Model\Config $config,
        \OM\Nospam\Actions\DecryptTime $decryptTime
    ) {
        $this->_request = $request;
        $this->_response = $response;
        $this->_actionFlag = $actionFlag;
        $this->_url = $url;
        $this->_redirect = $redirect;
        $this->_redirectFactory = $redirectFactory;
        $this->_messageManager = $messageManager;
        $this->_blacklist = $blacklist;
        $this->_suspiciousUrl = $suspiciousUrl;
        $this->_domain = $domain;
        $this->_config = $config;
        $this->_decryptTime = $decryptTime;
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

        /**
         * Already blacklisted?
         */
        /*
        if ($this->_blacklist->isBlacklisted()) {
            $this->_statusCode = 404;
            $this->_redirectUrl = $this->_getDenyUrl();
            $this->_redirect();
            return;
        }*/


        /**
         * Timestamps
         */
        if ($this->_config->useFormTimestamps()) {
            if (!$this->_checkFormTimestamp()) {
                $this->_redirect($this->_getDenyUrl(), 404);
                return;
            }
        }

        /**
         * Honeypots
         */
        if ($this->_config->useFormHoneypots()) {
            if (!$this->_checkFormHoneypot()) {
                $this->_redirect($this->_getDenyUrl(), 404);
                return;
            }
        }

        /**
         * Regex
         */
        if ($this->_config->useFormRegex()) {
            if (!$this->_checkRegex()) {
                $this->_redirect($this->_getDenyUrl(), 404);
                return;
            }
        }

        /**
         * Blacklisted mail domain
         */
        if ($this->_config->checkBlacklistedMailDomains()) {
            if ($this->_isEmailDomainBlacklisted()) {
                $this->_messageManager->addErrorMessage(__('This email domain is not allowed.'));
                $this->_redirect($this->_redirect->getRefererUrl(), 400);
                return;
            }
        }

        /**
         * Check for suspicious url.
         */
        if ($this->_config->checkSuspiciousUrlParts() && !$this->_suspiciousUrlChecked) {
            if ($this->_isSuspicious()) {
                $this->_suspiciousUrl->add($this->_url->getCurrentUrl());
            }

            /**
             * Prevent multiple executions in one dispatch cycle
             */
            $this->_suspiciousUrlChecked = true;
        }
    }

    /**
     * @return bool
     */
    protected function _isSuspicious(): bool
    {
        $result = false;
        $parts = $this->_config->getSuspiciousUrlParts();

        if (!empty($parts)) {
            $url = strtolower($this->_url->getCurrentUrl());

            foreach ($parts as $part) {
                $part = strtolower($part);

                if (strpos($url, $part) !== false) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * @return array|string|string[]
     */
    protected function _getUri()
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

        if ($email && $this->_domain->isBlacklisted($email)) {
            $result = true;
            $this->_blacklist->add('Blacklisted mail domain: ' . $email);
        }

        return $result;
    }

    /**
     * @return bool
     * @see \OM\Nospam\ViewModel\Template::getFormTimestamp()
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
                    $this->_blacklist->add('Timestamp in ' . $this->_getUri());
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

                    if (!empty($post[$name])) {
                        $this->_blacklist->add('Honeypot: ' . $name . ' in URI: ' . $uri);
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
     * If any expression matches the user ip will be added to the blacklist
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
                if (preg_match($expression, $field)) {
                    $this->_blacklist->add('Regex: ' . $name . '(' . $expression . ')');
                    $result = false;
                    break 2;
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
        $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
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