<?php

namespace OM\Nospam\Model\ResourceModel\Blacklist;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'om_nospam_blacklist_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'om_nospam_blacklist_collection';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \OM\Nospam\Model\Blacklist::class,
            \OM\Nospam\Model\ResourceModel\Blacklist::class
        );
    }
}