<?php
declare(strict_types=1);

namespace OM\Nospam\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use OM\Nospam\Model\ResourceModel\Log as ResourceModelLog;

class Log extends AbstractModel implements IdentityInterface
{
    /**
     *
     */
    const CACHE_TAG = 'om_nospam_log';

    /**
     * @var string
     */
    protected $_eventPrefix = 'om_nospam_log';

    /**
     *
     */
    protected function _construct() {
        $this->_init(ResourceModelLog::class);
    }

    /**
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}