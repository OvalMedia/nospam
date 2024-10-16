<?php
declare(strict_types=1);

namespace OM\Nospam\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Url extends AbstractModel implements IdentityInterface
{
    /**
     *
     */
    const CACHE_TAG = 'om_nospam_url';

    /**
     * @var string
     */
    protected $_eventPrefix = 'om_nospam_url';

    /**
     *
     */
    protected function _construct() {
        $this->_init(ResourceModel\Url::class);
    }

    /**
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}