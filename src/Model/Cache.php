<?php
declare(strict_types=1);

namespace OM\Nospam\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class Cache
{
    const CACHE_KEY = 'om_nospam_ip';
    const CACHE_TAG = 'OM_NOSPAM_IP';
    const CACHE_LIFETIME = 86400;


    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected AdapterInterface $_db;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected CacheInterface $_cache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected SerializerInterface $_serializer;

    /**
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        ResourceConnection $connection,
        CacheInterface $cache,
        SerializerInterface $serializer
    ){
        $this->_db = $connection->getConnection('default');
        $this->_cache = $cache;
        $this->_serializer = $serializer;
    }

    /**
     * @return void
     */
    public function refresh(): void
    {
        try {
            $ips = array();
            $res = $this->_db->query("SELECT ip FROM om_nospam_blacklist");

            while ($ip = $res->fetchColumn()) {
                $ips[] = $ip;
            }

            $this->_cache->save(
                $this->_serializer->serialize($ips),
                self::CACHE_KEY,
                [self::CACHE_TAG],
                self::CACHE_LIFETIME
            );
        } catch (\Exception $e) {}
    }
}