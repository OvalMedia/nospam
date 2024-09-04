<?php

namespace OM\Nospam\Model\ResourceModel\Url;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'om_nospam_url_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'om_nospam_url_collection';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \OM\Nospam\Model\Url::class,
            \OM\Nospam\Model\ResourceModel\Url::class
        );
    }
}