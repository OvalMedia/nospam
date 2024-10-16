<?php
declare(strict_types=1);

namespace OM\Nospam\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use OM\Nospam\Api\BlacklistInterface;
use OM\Nospam\Api\DomainInterface;
use OM\Nospam\Model\Config;

class CheckoutSubmitBefore implements ObserverInterface
{
    /**
     * @var \OM\Nospam\Api\BlacklistInterface
     */
    protected BlacklistInterface $_blacklist;

    /**
     * @var \OM\Nospam\Api\DomainInterface
     */
    protected DomainInterface $_domain;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @var array
     */
    protected array $_lastError = [];

    /**
     * @param \OM\Nospam\Api\BlacklistInterface $blacklist
     * @param \OM\Nospam\Api\DomainInterface $domain
     * @param \OM\Nospam\Model\Config $config
     */
    public function __construct(
        BlacklistInterface $blacklist,
        DomainInterface $domain,
        Config $config
    ) {
        $this->_blacklist = $blacklist;
        $this->_domain = $domain;
        $this->_config = $config;
    }

    /**
     * This event is triggered by webapi_rest/events.xml.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_config->isModuleEnabled()) {
            return;
        }

        if ($this->_blacklist->isBlacklisted()) {
            $this->_deny([__(BlacklistInterface::ERROR_MSG_BLACKLISTED)]);
            return;
        }

        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getQuote();

        $email = $quote->getCustomerEmail();

        if ($this->_domain->isBlacklisted($email)) {
            [,$domain] = explode('@', $email);
            $this->_deny([DomainInterface::ERROR_MSG_DOMAIN_DENIED, '@' . $domain]);
        }

        if (!$this->_checkAddressFields($quote)) {
            $this->_deny($this->_lastError);
        }

        if (!$this->_checkRegex($quote)) {
            $this->_deny($this->_lastError);
        }
    }

    /**
     * @param $quote
     *
     * @return bool
     */
    protected function _checkRegex($quote): bool
    {
        $result = true;
        $regex = $this->_config->getRegex();
        $excludes = $this->_config->getExcludeFromRegex();

        $addresses = [
            $quote->getBillingAddress(),
            $quote->getShippingAddress()
        ];

        /** @var $address \Magento\Quote\Model\Quote\Address */
        foreach ($addresses as $address) {
            foreach ($address->getData() as $key => $value) {
                if (empty($value) || in_array($key, $excludes)) {
                    continue;
                }

                foreach ($regex as $name => $expression) {
                    if (@preg_match($expression, $value)) {
                        $result = false;
                        if (!$this->_blacklist->isBlacklisted()) {
                            $this->_blacklist->add('Regex: ' . $name . '(' . $expression . ')');
                        }
                        $this->_lastError = ["Forbidden characters have been found in '%1'.", $key];
                        break 3;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param $quote
     *
     * @return bool
     */
    protected function _checkAddressFields($quote): bool
    {
        $result = true;
        $addressConfig = $this->_config->getAddressConfig();

        $addresses = [
            $quote->getBillingAddress(),
            $quote->getShippingAddress()
        ];

        /** @var $address \Magento\Quote\Model\Quote\Address */
        foreach ($addresses as $address) {
            foreach ($address->getData() as $key => $value) {
                if (isset($addressConfig[$key]) && (strlen($value) > $addressConfig[$key])) {
                    $this->_lastError = [
                        "The length of the address field '%1' exceeds the given limit (%2).",
                        $key,
                        $addressConfig[$key]
                    ];
                    $result = false;
                    break 2;
                }
            }
        }

        return $result;
    }

    /**
     * @param array $message
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _deny(array $message)
    {
        throw new LocalizedException(__(...$message));
    }
}