<?php

namespace OM\Nospam\Model;

class Url extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     *
     */
    const CACHE_TAG = 'om_nospam_url';

    /**
     * @var string
     */
    protected $_eventPrefix = 'om_nospam_url';

    /**
     *
     */
    protected function _construct() {
        $this->_init(\OM\Nospam\Model\ResourceModel\Url::class);
    }

    /**
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}