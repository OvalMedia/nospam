<?php
declare(strict_types=1);

namespace OM\Nospam\Api\Data;

interface DomainInterface
{
    const ENTITY_ID = 'entity_id';
    const NAME = 'name';

    public const ERROR_MSG_DOMAIN_DENIED = "Your email domain '%1' is not allowed. Please pick another email address.";

    /**
     * @return int
     */
    public function getEntityId(): int;

    /**
     * @param string $name
     * @return \OM\Nospam\Api\Data\DomainInterface
     */
    public function setName(string $name): DomainInterface;

    /**
     * @return string
     */
    public function getName(): string;
}