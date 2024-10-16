<?php
declare(strict_types=1);

namespace OM\Nospam\Plugin;

use Magento\Framework\UrlInterface;
use Magento\Robots\Model\Robots as RobotsCore;
use OM\Nospam\Model\Config;

class Robots
{
    /**
     * @var \OM\Nospam\Config
     */
    protected Config $_config;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected UrlInterface $_url;

    /**
     * @param \OM\Nospam\Config $config
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        Config $config,
        UrlInterface $url
    ) {
        $this->_config = $config;
        $this->_url = $url;
    }

    /**
     * @param \Magento\Robots\Model\Robots $robots
     * @param string|null $result
     * @return string|null
     */
    public function afterGetData(RobotsCore $robots, ?string $result): ?string
    {
        if ($this->_config->showHoneypotUrl()) {
            $urlkey = $this->_config->getHoneypotUrlKey();

            if ($urlkey) {
                $url = $this->_url->getUrl($urlkey);
                $baseUrl = $this->_url->getBaseUrl();

                if (strpos($url, $baseUrl) === 0) {
                    $url = substr($url, strlen($baseUrl));
                }

                if (!empty($result)) {
                    $result .= "\n";
                }

                $result .= "User-agent: * \n";
                $result .= "Disallow: /" . $url . "\n";
            }
        }

        return $result;
    }
}