<?php

namespace OM\Nospam\Model\ResourceModel;

class Url extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('om_nospam_urls', 'entity_id');
    }
}