<?php
declare(strict_types=1);

namespace OM\Nospam\Model\ResourceModel\Domain;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use OM\Nospam\Model\Domain;
use OM\Nospam\Model\ResourceModel\Domain as ResourceModelDomain;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'om_nospam_domains_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'om_nospam_domains_collection';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            Domain::class,
            ResourceModelDomain::class
        );
    }
}