<?php
namespace OM\Nospam\Observer;

class CheckoutSubmitBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \OM\Nospam\Api\BlacklistInterface
     */
    protected \OM\Nospam\Api\BlacklistInterface $_blacklist;

    /**
     * @var \OM\Nospam\Api\DomainInterface
     */
    protected \OM\Nospam\Api\DomainInterface $_domain;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected \OM\Nospam\Model\Config $_config;

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
        \OM\Nospam\Api\BlacklistInterface $blacklist,
        \OM\Nospam\Api\DomainInterface $domain,
        \OM\Nospam\Model\Config $config
    ) {
        $this->_blacklist = $blacklist;
        $this->_domain = $domain;
        $this->_config = $config;
    }

    /**
     *
     * Dieser Event wird in webapi_rest/events.xml gefeuert.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_config->isModuleEnabled()) {
            return;
        }

        if ($this->_blacklist->isBlacklisted()) {
            $this->_deny([__('You have been blacklisted.')]);
            return;
        }

        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getQuote();

        $email = $quote->getCustomerEmail();

        if ($this->_domain->isBlacklisted($email)) {
            [,$domain] = explode('@', $email);
            $this->_deny(["Your email domain '%1' is not allowed. Please pick another email address.", '@' . $domain]);
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
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _deny(array $message)
    {
        throw new \Magento\Framework\Exception\LocalizedException(__(...$message));
    }
}