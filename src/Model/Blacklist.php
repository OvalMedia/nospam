<?php
declare(strict_types=1);

namespace OM\Nospam\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use OM\Nospam\Model\ResourceModel\Blacklist as ResourceModelBlacklist;

class Blacklist extends AbstractModel implements IdentityInterface
{
    /**
     *
     */
    const CACHE_TAG = 'om_nospam_blacklist';

    /**
     * @var string
     */
    protected $_eventPrefix = 'om_nospam_blacklist';

    /**
     *
     */
    protected function _construct() {
        $this->_init(ResourceModelBlacklist::class);
    }

    /**
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}