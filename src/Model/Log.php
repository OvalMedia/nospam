<?php
declare(strict_types=1);

namespace OM\Nospam\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use OM\Nospam\Model\ResourceModel\Log as ResourceModel;
use OM\Nospam\Api\Data\LogInterface;

class Log extends AbstractModel implements IdentityInterface, LogInterface
{
    const CACHE_TAG = 'om_nospam_log';

    /**
     * @var string
     */
    protected $_eventPrefix = 'om_nospam_log';

    /**
     *
     */
    protected function _construct()
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
     * @return string
     */
    public function getIp(): string
    {
        return $this->getData(LogInterface::IP);
    }

    /**
     * @param string $ip
     * @return \OM\Nospam\Api\Data\LogInterface
     */
    public function setIp(string $ip): LogInterface
    {
        return $this->setData(LogInterface::IP, $ip);
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->getData(LogInterface::DATE);
    }

    /**
     * @param string $date
     * @return \OM\Nospam\Api\Data\LogInterface
     */
    public function setDate(string $date): LogInterface
    {
        return $this->setData(LogInterface::DATE, $date);
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->getData(LogInterface::COMMENT);
    }

    /**
     * @param string $comment
     * @return \OM\Nospam\Api\Data\LogInterface
     */
    public function setComment(string $comment): LogInterface
    {
        return $this->setData(LogInterface::COMMENT, $comment);
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->getData(LogInterface::USER_AGENT);
    }

    /**
     * @param string $userAgent
     * @return \OM\Nospam\Api\Data\LogInterface
     */
    public function setUserAgent(string $userAgent): LogInterface
    {
        return $this->setData(LogInterface::USER_AGENT, $userAgent);
    }

    /**
     * @return string
     */
    public function getRequest(): string
    {
        return $this->getData(LogInterface::REQUEST);
    }

    /**
     * @param string $request
     * @return \OM\Nospam\Api\Data\LogInterface
     */
    public function setRequest(string $request): LogInterface
    {
        return $this->setData(LogInterface::REQUEST, $request);
    }
}