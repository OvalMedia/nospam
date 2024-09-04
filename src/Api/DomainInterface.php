<?php

namespace OM\Nospam\Api;

interface DomainInterface
{
    /**
     * @param string $mail
     *
     * @return bool
     */
    public function isBlacklisted(string $mail): bool;
}