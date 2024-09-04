<?php

namespace OM\Nospam\Model\ResourceModel;

class Blacklist extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init('om_nospam_blacklist', 'entity_id');
    }
}