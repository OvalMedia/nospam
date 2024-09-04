<?php

namespace OM\Nospam\Api;

class Url implements \OM\Nospam\Api\UrlInterface
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected \Magento\Framework\DB\Adapter\AdapterInterface $_db;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected \Magento\Framework\App\CacheInterface $_cache;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $_remoteAddress;

    /**
     * @var \OM\Nospam\Model\Config
     */
    protected \OM\Nospam\Model\Config $_config;

    /**
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
    ){
        $this->_db = $connection->getConnection('default');
        $this->_cache = $cache;
        $this->_remoteAddress = $remoteAddress;
    }


    /**
     * @param string $url
     * @return void
     */
    public function add(string $url)
    {
        try {
            $this->_db->query(
                "INSERT INTO om_nospam_suspicious_urls SET ip = :ip, url = :url",
                [
                    'ip' => $this->_remoteAddress->getRemoteAddress(),
                    'url' => $url
                ]
            );
        } catch (\Exception $e) {}
    }
}