<?php

namespace OM\Nospam\Api;

interface BlacklistInterface
{
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