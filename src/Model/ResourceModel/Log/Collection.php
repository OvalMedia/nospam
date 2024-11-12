<?php
declare(strict_types=1);

namespace OM\Nospam\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use OM\Nospam\Model\Log;
use OM\Nospam\Model\ResourceModel\Log as ResourceModelLog;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'om_nospam_log_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'om_nospam_log_collection';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            Log::class,
            ResourceModelLog::class
        );
    }
}