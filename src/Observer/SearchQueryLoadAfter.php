<?php
declare(strict_types=1);

namespace OM\Nospam\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Search\Model\Query;
use Magento\Framework\UrlInterface;
use Magento\Search\Helper\Data as SearchHelper;
use OM\Nospam\Model\Config;

class SearchQueryLoadAfter implements ObserverInterface
{
    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected URLInterface $_url;

    /**
     * @var \Magento\Search\Helper\Data
     */
    protected SearchHelper $_searchHelper;

    /**
     * @param \OM\Nospam\Model\Config $config
     * @param \Magento\Search\Helper\Data $searchHelper
     */
    public function __construct(
        Config $config,
        UrlInterface $url,
        SearchHelper $searchHelper
    ) {
        $this->_config = $config;
        $this->_url = $url;
        $this->_searchHelper = $searchHelper;
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

        /** @var $query Query */
        $query = $observer->getSearchQuery();
        $redirect = $query->getRedirect();
        $baseUrl = $this->_url->getBaseUrl();
        $searchUrl = '/' . str_replace($baseUrl, '', $this->_searchHelper->getResultUrl());

        /**
         * Modify query only if redirect target is a search result page.
         */
        if (stripos($redirect, $searchUrl) !== false) {
            $fieldName = $this->_config->getFieldnameByFormAction($searchUrl);

            if (!empty($redirect) && !empty($fieldName) && stripos($redirect, $fieldName) === false) {
                $query->setRedirect($redirect . '&' . $fieldName . '=');
            }
        }
    }
}