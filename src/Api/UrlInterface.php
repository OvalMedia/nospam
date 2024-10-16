<?php
declare(strict_types=1);

namespace OM\Nospam\Api;

interface UrlInterface
{
    /**
     * @param string $url
     * @return mixed
     */
    public function add(string $url);
}