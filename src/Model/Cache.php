<?php

namespace OM\Nospam\Model;

class Cache
{
    const CACHE_KEY = 'om_nospam_ip';
    const CACHE_TAG = 'OM_NOSPAM_IP';
    const CACHE_LIFETIME = 86400;


    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected \Magento\Framework\DB\Adapter\AdapterInterface $_db;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected \Magento\Framework\App\CacheInterface $_cache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected \Magento\Framework\Serialize\SerializerInterface $_serializer;

    /**
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ){
        $this->_db = $connection->getConnection('default');
        $this->_cache = $cache;
        $this->_serializer = $serializer;
    }

    /**
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function refresh()
    {
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
    }
}