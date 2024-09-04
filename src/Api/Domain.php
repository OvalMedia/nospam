<?php

namespace OM\Nospam\Api;

class Domain implements \OM\Nospam\Api\DomainInterface
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected \Magento\Framework\DB\Adapter\AdapterInterface $_db;

    /**
     * @param \Magento\Framework\App\ResourceConnection $connection
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $connection
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