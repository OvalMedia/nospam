<?php
declare(strict_types=1);

namespace OM\Nospam\Api;

interface DomainInterface
{
    public const ERROR_MSG_DOMAIN_DENIED = "Your email domain '%1' is not allowed. Please pick another email address.";

    /**
     * @param string $mail
     *
     * @return bool
     */
    public function isBlacklisted(string $mail): bool;
}