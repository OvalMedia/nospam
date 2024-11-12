<?php
declare(strict_types=1);

namespace OM\Nospam\Api;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\Header;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

use OM\Nospam\Model\Cache\Type\Log as CacheType;
use OM\Nospam\Api\LogInterface;
use OM\Nospam\Model\Config;

class Log implements LogInterface
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected AdapterInterface $_db;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected CacheInterface $_cache;

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
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\HTTP\Header $header
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \OM\Nospam\Model\Config $config
     */
    public function __construct(
        ResourceConnection $connection,
        CacheInterface $cache,
        RequestInterface $request,
        Header $header,
        RemoteAddress $remoteAddress,
        SerializerInterface $serializer,
        Config $config
    ){
        $this->_db = $connection->getConnection('default');
        $this->_cache = $cache;
        $this->_request = $request;
        $this->_header = $header;
        $this->_remoteAddress = $remoteAddress;
        $this->_serializer = $serializer;
        $this->_config = $config;
    }

    /**
     * @return bool
     */
    public function isBlacklisted(): bool
    {
        $result = false;

        try {
            if ($ip = $this->_getCurrentIp()) {
                $data = $this->_getData();
                $max = $this->_config->getMaxLogEntries();

                if (isset($data[$ip]) && count($data[$ip]) >= $max) {
                    $result = true;
                }
            }
        } catch (\Exception $e) {}

        return $result;
    }

    /**
     * @param string|null $type
     *
     * @return void
     */
    public function add(?string $comment = null)
    {
        try {
            $this->_db->query(
                "INSERT INTO om_nospam_log
                SET ip = :ip, 
                `comment` = :comment, 
                `user_agent` = :user_agent,
                `request` = :request",
                array(
                    'ip' => $this->_getCurrentIp(),
                    'comment' => $comment,
                    'user_agent' => $this->_header->getHttpUserAgent(),
                    'request' => json_encode($this->_getParams())
                )
            );

            $this->_loadFromDb();
        } catch (\Zend_Db_Statement_Exception $e) {
            die($e->getMessage());
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
    public function cleanup()
    {
        $lifetime = $this->_config->getLogLifetime();

        if ($lifetime > 0) {
            try {
                $date = new \DateTime();
                $date->modify('-' . $lifetime . ' day');

                $this->_db->query(
                    "DELETE FROM om_nospam_log WHERE `date` <= :date", [
                    'date' => $date->format('Y-m-d H:i:s')
                ]);

                $this->_loadFromDb();
            } catch (\Zend_Db_Statement_Exception $e) {
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

    /**
     * @return array|bool|float|int|string|null
     * @throws \Zend_Db_Statement_Exception
     */
    protected function _getData()
    {
        $data = $this->_cache->load(CacheType::CACHE_KEY);

        if ($data) {
            $data = $this->_serializer->unserialize($data);
        } else {
            $data = $this->_loadFromDb();
        }

        return $data;
    }

    /**
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    protected function _loadFromDb(): array
    {
        $data = array();
        $res = $this->_db->query(
            "SELECT `ip`, `date` 
            FROM om_nospam_log
            ORDER BY `ip`, `date` DESC"
        );

        while ($row = $res->fetchObject()) {
            $data[$row->ip][] = $row->date;
        }

        if (!empty($data)) {
            $this->_cache->save(
                $this->_serializer->serialize($data),
                CacheType::CACHE_KEY,
                [CacheType::CACHE_TAG],
                CacheType::CACHE_LIFETIME
            );
        }

        return $data;
    }
}