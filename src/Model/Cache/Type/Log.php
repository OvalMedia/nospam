<?php
declare(strict_types=1);

namespace OM\Nospam\Model\Cache\Type;

use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\App\Cache\Type\FrontendPool;

class Log extends TagScope
{
    const CACHE_KEY = 'om_nospam_ip';
    const CACHE_TAG = 'OM_NOSPAM_IP';
    const CACHE_LIFETIME = 86400;

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $frontendPool
     */
    public function __construct(
        FrontendPool $frontendPool
    ) {
        parent::__construct($frontendPool->get(self::CACHE_KEY), self::CACHE_TAG);
    }
}