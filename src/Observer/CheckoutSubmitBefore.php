<?php
declare(strict_types=1);

namespace OM\Nospam\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;
use OM\Nospam\Api\Data\LogInterface;
use OM\Nospam\Service\LogService;
use OM\Nospam\Service\DomainService;
use OM\Nospam\Api\Data\DomainInterface;
use OM\Nospam\Model\Config;

class CheckoutSubmitBefore implements ObserverInterface
{
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
     * @var array
     */
    protected array $_lastError = [];

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \OM\Nospam\Service\LogService $logService
     * @param \OM\Nospam\Service\DomainService $domainService
     * @param \OM\Nospam\Model\Config $config
     */
    public function __construct(
        LoggerInterface $logger,
        LogService $logService,
        DomainService $domainService,
        Config $config
    ) {
        $this->_logger = $logger;
        $this->_logService = $logService;
        $this->_domainService = $domainService;
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

        if ($this->_logService->isBlacklisted()) {
            $error = __(LogInterface::ERROR_MSG_BLACKLISTED, $this->_config->getMaxLogTimePeriod())->render();
            $this->_logService->add($error);
            $this->_deny([$error]);
            return;
        }

        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getQuote();

        $email = $quote->getCustomerEmail();

        if ($this->_domainService->isBlacklisted($email)) {
            [,$domain] = explode('@', $email);
            $this->_logService->add('Blacklisted email domain: ' . '@' . $domain);
            $this->_deny([DomainInterface::ERROR_MSG_DOMAIN_DENIED, '@' . $domain]);
        }

        if (!$this->_checkAddressFields($quote)) {
            $error = '';
            if (is_array($this->_lastError) && count($this->_lastError)) {
                $error = __(...$this->_lastError);
            }
            $this->_logService->add('Address field violation in quote ID ' . $quote->getId() . ': ' . $error);
            $this->_deny($this->_lastError);
        }

        if (!$this->_checkRegex($quote)) {
            $this->_deny($this->_lastError);
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    protected function _checkRegex(Quote $quote): bool
    {
        $result = true;
        $regex = $this->_config->getRegex();
        $excludes = $this->_config->getExcludeFromRegex();

        $addresses = [
            $quote->getBillingAddress(),
            $quote->getShippingAddress()
        ];

        $addressConfig = $this->_config->getAddressConfig();
        $addressFields = array_keys($addressConfig);

        /** @var $address \Magento\Quote\Model\Quote\Address */
        foreach ($addresses as $address) {
            foreach ($address->getData() as $key => $value) {
                if (empty($value) || in_array($key, $excludes) || !in_array($key, $addressFields)) {
                    continue;
                }

                $value = (string) $value;

                foreach ($regex as $name => $expression) {
                    $check = false;

                    try {
                        $check = preg_match($expression, $value);
                    } catch (\Exception $e) {
                        $this->_logger->critical($e->getMessage());
                    }

                    if ($check !== false && $check !== 0) {
                        $result = false;
                        if (!$this->_logService->isBlacklisted()) {
                            $this->_logService->add('Regex: ' . $name . ' (' . $expression . ') in "' . $key . '": "' . $value . '"');
                        }
                        $this->_lastError = ["Forbidden characters have been found in '%1'.", __($key)];
                        break 3;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    protected function _checkAddressFields(Quote $quote): bool
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
                $value = $value ? $value : '';
                if (isset($addressConfig[$key]) && (strlen($value) > $addressConfig[$key])) {
                    $this->_lastError = [
                        "The length of the field '%1' exceeds the given limit (%2).",
                        __($key),
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