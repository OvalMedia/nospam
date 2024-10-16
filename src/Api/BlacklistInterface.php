<?php
declare(strict_types=1);

namespace OM\Nospam\Api;

interface BlacklistInterface
{
    public const ERROR_MSG_BLACKLISTED = 'You have been blacklisted.';

    /**
     * @return bool
     */
    public function isBlacklisted(): bool;

    /**
     * @param string|null $type
     *
     * @return mixed
     */
    public function add(?string $comment = null);
}