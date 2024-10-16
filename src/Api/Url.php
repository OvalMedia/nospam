<?php
declare(strict_types=1);

namespace OM\Nospam\Api;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Url implements UrlInterface
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
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected RemoteAddress $_remoteAddress;

    /**
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     */
    public function __construct(
        ResourceConnection $connection,
        CacheInterface $cache,
        RemoteAddress $remoteAddress
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