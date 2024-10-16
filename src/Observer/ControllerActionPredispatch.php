<?php
declare(strict_types=1);

namespace OM\Nospam\Observer;

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
use OM\Nospam\Api\BlacklistInterface;
use OM\Nospam\Api\DomainInterface;
use OM\Nospam\Api\UrlInterface as NospamUrlInterface;
use OM\Nospam\Model\Config;
use OM\Nospam\Actions\DecryptTime;

class ControllerActionPredispatch implements ObserverInterface
{
    const FLAG_SUSPICIOUS_URL = 'flag_suspicious_url_checked';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected RequestInterface $_request;

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
     * @var \OM\Nospam\Api\BlacklistInterface
     */
    protected BlacklistInterface $_blacklist;

    /**
     * @var \OM\Nospam\Api\UrlInterface
     */
    protected NospamUrlInterface $_suspiciousUrl;

    /**
     * @var \OM\Nospam\Api\DomainInterface
     */
    protected DomainInterface $_domain;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @var \OM\Nospam\Actions\DecryptTime
     */
    protected DecryptTime $_decryptTime;

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
        RequestInterface $request,
        ResponseInterface $response,
        ActionFlag $actionFlag,
        UrlInterface $url,
        RedirectInterface $redirect,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        BlacklistInterface $blacklist,
        NospamUrlInterface $suspiciousUrl,
        DomainInterface $domain,
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

        if ($email && $this->_domain->isBlacklisted($email)) {
            $result = true;
            $this->_blacklist->add('Blacklisted mail domain: ' . $email);
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

                    if (!isset($post[$name]) || !empty($post[$name])) {
                        $this->_blacklist->add("Honeypot: '$name' in URI: $uri");
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