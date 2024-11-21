<?php
declare(strict_types=1);

namespace OM\Nospam\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use OM\Nospam\Api\DomainRepositoryInterface;

class DomainService
{
    /**
     * @var \OM\Nospam\Api\DomainRepositoryInterface
     */
    protected DomainRepositoryInterface $_domainRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $_logger;

    /**
     * @param \OM\Nospam\Api\DomainRepositoryInterface $domainRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        DomainRepositoryInterface $domainRepository,
        LoggerInterface $logger
    ) {
        $this->_domainRepository = $domainRepository;
        $this->_logger = $logger;
    }

    /**
     * Determines if a given email is blacklisted by checking its domain.
     *
     * @param string $mail The email address to check.
     * @return bool Returns true if the email's domain is blacklisted, otherwise false.
     */
    public function isBlacklisted(string $mail): bool
    {
        $result = false;
        [, $domainname] = explode('@', $mail);

        try {
            $domain = $this->_domainRepository->getByName($domainname);
            if ($domain->getEntityId()) {
                $result = true;
            }
        } catch (NoSuchEntityException $e) {}

        return $result;
    }
}