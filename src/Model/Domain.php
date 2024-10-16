<?php
declare(strict_types=1);

namespace OM\Nospam\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Domain extends AbstractModel implements IdentityInterface
{
    /**
     *
     */
    const CACHE_TAG = 'om_nospam_domain';

    /**
     * @var string
     */
    protected $_eventPrefix = 'om_nospam_domain';

    /**
     *
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Domain::class);
    }

    /**
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}