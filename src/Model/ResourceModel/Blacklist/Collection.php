<?php
declare(strict_types=1);

namespace OM\Nospam\Model\ResourceModel\Blacklist;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use OM\Nospam\Model\Blacklist;
use OM\Nospam\Model\ResourceModel\Blacklist as ResourceModelBlacklist;

class Collection extends AbstractCollection
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
    protected function _construct(): void
    {
        $this->_init(
            Blacklist::class,
            ResourceModelBlacklist::class
        );
    }
}