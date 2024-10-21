<?php
declare(strict_types=1);

namespace OM\Nospam\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Escaper;
use OM\Nospam\Model\Config;
use OM\Nospam\Actions\EncryptTime;

class Nospam implements ArgumentInterface
{
    /**
     * @var \OM\Nospam\Model\Config
     */
    protected Config $_config;

    /**
     * @var \OM\Nospam\Actions\EncryptTime
     */
    protected EncryptTime $_encryptTime;

    /**
     * @var int|null
     */
    protected $_timestamp;

    /**
     * @var array
     */
    protected $_honeypotActions;

    /**
     * @var
     */
    protected $_honeypotData;

    /**
     * @var array
     */
    protected $_timestampActions;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected \Magento\Framework\Escaper $_escaper;

    /**
     * @param \OM\Nospam\Model\Config $config
     * @param \OM\Nospam\Actions\EncryptTime $encryptTime
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        Config $config,
        EncryptTime $encryptTime,
        Escaper $escaper
    ) {
        $this->_config = $config;
        $this->_encryptTime = $encryptTime;
        $this->_escaper = $escaper;
    }

    /**
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->_config->isModuleEnabled();
    }

    /**
     * @return bool
     */
    public function showHoneypotUrl(): bool
    {
        return $this->_config->showHoneypotUrl();
    }

    /**
     * @return string|null
     */
    public function getHoneypotUrlKey(): ?string
    {
        $url = false;

        if ($this->showHoneypotUrl()) {
            $url = $this->_config->getHoneypotUrlKey();
        }

        return $url;
    }

    /**
     * @return string|null
     */
    public function getHoneypotUrlText(): ?string
    {
        $text = false;

        if ($this->showHoneypotUrl()) {
            $text = $this->_config->getHoneypotUrlText();
        }

        return $text;
    }

    /**
     * @param $field
     *
     * @return bool
     */
    public function isFieldEnabled($field): bool
    {
        return $this->_config->isFieldEnabled($field);
    }

    /**
     * @param $type
     * @return string|null
     */
    public function getFieldTitle($type): ?string
    {
        return $this->_config->getFieldTitle($type);
    }

    /**
     * @param $type
     *
     * @return string|null
     */
    public function getFieldId($type): ?string
    {
        return $this->_config->getFieldId($type);
    }

    /**
     * @param $type
     * @return string|null
     */
    public function getFieldName($type): ?string
    {
        return $this->_config->getFieldName($type);
    }

    /**
     * @return string
     */
    public function getRandomId(): string
    {
        $length = rand(8, 12);
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = $characters[rand(0, strlen($characters) - 1)];

        for ($i = 1; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * @return bool
     */
    public function useFormTimestamps(): bool
    {
        $iv = $this->_config->getTimestampIV();
        $passphrase = $this->_config->getTimestampPassphrase();
        $threshold = $this->_config->getTimestampThreshold();
        return ($iv && $passphrase && $threshold && $this->_config->useFormTimestamps());
    }

    /**
     * @return bool
     */
    public function useFormHoneypots(): bool
    {
        $actions = $this->getFormHoneypotActions();
        return (!empty($actions) && $this->_config->useFormHoneypots());
    }

    /**
     * @return int
     */
    public function getFormTimestamp(): string
    {
        if ($this->_timestamp === null) {
            $this->_timestamp = $this->_encryptTime->execute();
        }

        return $this->_timestamp;
    }

    /**
     * @return string
     */
    public function getTimestampFieldName(): string
    {
        return $this->_config->getTimestampFieldName();
    }

    /**
     * @return array
     */
    public function getFormActions(): array
    {
        $result = [];

        if ($actions = $this->getFormHoneypotActions()) {
            $result = $actions;
        }

        if ($actions = $this->getFormTimestampActions()) {
            $result = array_merge($result, $actions);
        }

        return array_unique($result);
    }

    /**
     * @return array
     */
    public function getFormHoneypotActions(): array
    {
        if ($this->_honeypotActions === null) {
            $this->_honeypotActions = [];

            if ($actions = $this->_config->getFormHoneypotActions()) {
                foreach ($actions as $key => $row) {
                    $this->_honeypotActions[] = "'" . $row['action'] . "'";
                }
            }
        }

        return $this->_honeypotActions;
    }

    /**
     * @return array
     */
    public function getFormHoneypotData(): array
    {
        if ($this->_honeypotData === null) {
            $this->_honeypotData = [];

            if ($actions = $this->_config->getFormHoneypotActions()) {
                foreach ($actions as $key => $row) {
                    $name = str_replace(' ', '-', strtolower($row['name']));
                    $this->_honeypotData[$row['action']] = [
                        'name' => $name,
                        'title' => $row['name']
                    ];
                }
            }
        }

        return $this->_honeypotData;
    }

    /**
     * @return array
     */
    public function getFormTimestampActions(): array
    {
        if ($this->_timestampActions === null) {
            $this->_timestampActions = [];
            if ($actions = $this->_config->getFormTimestampActions()) {
                foreach ($actions as $key => $action) {
                    $this->_timestampActions[] = "'" . $action . "'";
                }
            }
        }

        return $this->_timestampActions;
    }
}