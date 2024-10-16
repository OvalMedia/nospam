<?php
declare(strict_types=1);

namespace OM\Nospam\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Url extends AbstractDb
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