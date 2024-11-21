<?php
declare(strict_types=1);

namespace OM\Nospam\Service;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\Header;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;

use OM\Nospam\Model\LogFactory;
use OM\Nospam\Model\LogRepository;
use OM\Nospam\Model\ResourceModel\Log\CollectionFactory;
use OM\Nospam\Model\Config;

class LogService
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected RequestInterface $_request;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected Header $_header;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected RemoteAddress $_remoteAddress;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected SerializerInterface $_serializer;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @var \OM\Nospam\Model\ResourceModel\Log\CollectionFactory
     */
    protected CollectionFactory $_collectionFactory;

    /**
     * @var \OM\Nospam\Model\LogRepository
     */
    protected LogRepository $_logRepository;

    /**
     * @var \OM\Nospam\Model\LogFactory
     */
    protected LogFactory $_logFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $_logger;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\HTTP\Header $header
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \OM\Nospam\Model\Config $config
     * @param \OM\Nospam\Model\LogRepository $logRepository
     * @param \OM\Nospam\Model\LogFactory $logFactory
     * @param \OM\Nospam\Model\ResourceModel\Log\CollectionFactory $collectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        Header $header,
        RemoteAddress $remoteAddress,
        SerializerInterface $serializer,
        Config $config,
        LogRepository $logRepository,
        LogFactory $logFactory,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ){
        $this->_request = $request;
        $this->_header = $header;
        $this->_remoteAddress = $remoteAddress;
        $this->_serializer = $serializer;
        $this->_config = $config;
        $this->_logRepository = $logRepository;
        $this->_logFactory = $logFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_logger = $logger;
    }

    /**
     * @return bool
     */
    public function isBlacklisted(): bool
    {
        $result = false;

        try {
            if ($ip = $this->_getCurrentIp()) {
                $max = $this->_config->getMaxLogEntries();
                $hours = $this->_config->getMaxLogTimePeriod();
                $now = new \DateTime();

                $interval = new \DateInterval('PT' . $hours . 'H');
                $now->sub($interval);

                $collection = $this->_collectionFactory->create();
                $collection
                    ->addFieldToFilter('ip', $ip)
                    ->addFieldToFilter('date', ['gteq' => $now->format('Y-m-d H:i:s')])
                ;

                if ($collection->count() >= $max) {
                    $result = true;
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $result;
    }

    /**
     * @param string|null $type
     *
     * @return void
     */
    public function add(?string $comment = null): void
    {
        try {
            $log = $this->_logFactory->create();
            $log
                ->setIp($this->_getCurrentIp())
                ->setComment($comment)
                ->setUserAgent($this->_header->getHttpUserAgent())
                ->setRequest(json_encode($this->_getParams()))
            ;
            $this->_logRepository->save($log);
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
    }

    /**
     * Gets all POST/GET params from request and removes all
     * blacklisted parameters that should not be stored in the
     * database log (e.g. 'password').
     *
     * @return array
     */
    protected function _getParams(): array
    {
        $params = $this->_request->getParams();
        $remove = $this->_config->getRemoveFromRequestFields();

        foreach ($remove as $item) {
            $params = $this->_removeItem($params, $item);
        }

        return $params;
    }

    /**
     * @param array $array
     * @param string $item
     *
     * @return array
     */
    protected function _removeItem(array $array, string $item): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->_removeItem($value, $item);
            }

            if ($key == $item) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * @todo Handle Exception
     */
    public function cleanup(): void
    {
        $days = $this->_config->getLogLifetime();

        if ($days > 0) {
            try {
                $date = new \DateTime();
                $interval = new \DateInterval('P' . $days . 'D');
                $date->sub($interval);
                $collection = $this->_collectionFactory->create();
                $collection->addFieldToFilter('date', ['lteq' => $date]);
                $collection->walk('delete');
            } catch (\Exception $e) {
                $this->_logger->critical($e->getMessage());
            }
        }
    }

    /**
     * @return string|null
     */
    protected function _getCurrentIp(): ?string
    {
        return $this->_remoteAddress->getRemoteAddress();
    }
}