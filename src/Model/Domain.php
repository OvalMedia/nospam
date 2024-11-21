<?php
declare(strict_types=1);

namespace OM\Nospam\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use OM\Nospam\Model\ResourceModel\Log as ResourceModel;
use OM\Nospam\Api\Data\DomainInterface;

class Domain extends AbstractModel implements IdentityInterface, DomainInterface
{
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
        $this->_init(ResourceModel::class);
    }

    /**
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return (int) $this->getData(DomainInterface::ENTITY_ID);
    }

    /**
     * @param string $name
     * @return \OM\Nospam\Api\Data\DomainInterface
     */
    public function setName(string $name): DomainInterface
    {
        return $this->setData(DomainInterface::NAME, $name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }
}