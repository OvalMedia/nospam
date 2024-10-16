<?php
declare(strict_types=1);

namespace OM\Nospam\Api;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class Domain implements DomainInterface
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected AdapterInterface $_db;

    /**
     * @param \Magento\Framework\App\ResourceConnection $connection
     */
    public function __construct(
        ResourceConnection $connection
    ) {
        $this->_db = $connection->getConnection('default');
    }

    /**
     * @param string $mail
     *
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function isBlacklisted(string $mail): bool
    {
        [, $domain] = explode('@', $mail);
        $res = $this->_db->query(
            "SELECT COUNT(*) 
            FROM om_nospam_domains
            WHERE `domain` = :domain", [
                'domain' => $domain
            ]
        );

        return (bool) $res->fetchColumn();
    }
}