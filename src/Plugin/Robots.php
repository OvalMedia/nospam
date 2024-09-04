<?php

namespace OM\Nospam\Plugin;

class Robots
{
    /**
     * @var \OM\Nospam\Model\Config
     */
    protected \OM\Nospam\Model\Config $_config;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected \Magento\Framework\UrlInterface $_url;

    /**
     * @param \OM\Nospam\Model\Config $config
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \OM\Nospam\Model\Config $config,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->_config = $config;
        $this->_url = $url;
    }

    /**
     * @param \Magento\Robots\Model\Robots $robots
     * @param string|null $result
     *
     * @return string|null
     */
    public function afterGetData(\Magento\Robots\Model\Robots $robots, ?string $result): ?string
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