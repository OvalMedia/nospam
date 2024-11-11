<?php
declare(strict_types=1);

namespace OM\Nospam\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const CONFIG_PATH = 'om_nospam';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected ScopeConfigInterface $_scope;

    /**
     * @var array
     */
    protected $_formTimestampActions;

    /**
     * @var array
     */
    protected $_formHoneypotActions;

    /**
     * @var
     */
    protected $_honeypotData;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scope
     */
    public function __construct(
        ScopeConfigInterface $scope
    ) {
        $this->_scope = $scope;
    }

    /**
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return (bool) $this->_scope->getValue(
            self::CONFIG_PATH . '/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function showHoneypotUrl(): bool
    {
        return (bool) $this->_scope->getValue(
            self::CONFIG_PATH . '/bots/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string|null
     */
    public function getHoneypotUrlKey(): ?string
    {
        return $this->_scope->getValue(
            self::CONFIG_PATH . '/bots/url_key',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string|null
     */
    public function getHoneypotUrlText(): ?string
    {
        return $this->_scope->getValue(
            self::CONFIG_PATH . '/bots/url_text',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $field
     *
     * @return bool
     */
    public function isFieldEnabled($field): bool
    {
        return (bool) $this->_scope->getValue(
            self::CONFIG_PATH . '/honeypots/' . $field,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $type
     *
     * @return string|null
     */
    public function getFieldTitle(string $type): ?string
    {
        return $this->_scope->getValue(
            self::CONFIG_PATH . '/honeypots/' . $type . '_title',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $type
     *
     * @return string|null
     */
    public function getFieldId($type): ?string
    {
        return $this->_scope->getValue(
            self::CONFIG_PATH . '/honeypots/' . $type . '_id',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $type
     *
     * @return string|null
     */
    public function getFieldName($type): ?string
    {
        return $this->_scope->getValue(
            self::CONFIG_PATH . '/honeypots/' . $type . '_name',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getRemoveFromRequestFields(): array
    {
        $result = [];

        $fields = $this->_scope->getValue(
            self::CONFIG_PATH . '/misc/remove_from_request',
            ScopeInterface::SCOPE_STORE
        );

        if ($fields) {
            $fields = @json_decode($fields, true);

            if ($fields) {
                foreach ($fields as $field) {
                    $result[] = $field['field'];
                }
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function checkSuspiciousUrlParts(): bool
    {
        return (bool) $this->_scope->getValue(
            self::CONFIG_PATH . '/suspicious/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getSuspiciousUrlParts(): array
    {
        $result = [];

        $parts = $this->_scope->getValue(
            self::CONFIG_PATH . '/suspicious/urls',
            ScopeInterface::SCOPE_STORE
        );

        if ($parts) {
            $parts = @json_decode($parts, true);

            if ($parts) {
                foreach ($parts as $part) {
                    $result[] = $part['field'];
                }
            }
        }

        return $result;
    }

    /**
     * @param $form
     *
     * @return bool
     */
    public function checkForm($form): bool
    {
        return (bool) $this->_scope->getValue(
            self::CONFIG_PATH . '/forms/' . $form,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function useFormTimestamps(): bool
    {
        return (bool) $this->_scope->getValue(
            self::CONFIG_PATH . '/forms/timestamps/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function useFormHoneypots(): bool
    {
        return (bool) $this->_scope->getValue(
            self::CONFIG_PATH . '/forms/honeypots/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function useFormRegex(): bool
    {
        return (bool) $this->_scope->getValue(
            self::CONFIG_PATH . '/forms/regex/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int|null
     */
    public function getTimestampThreshold(): ?int
    {
        return (int) $this->_scope->getValue(
            self::CONFIG_PATH . '/forms/timestamps/threshold',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string|null
     */
    public function getTimestampIV(): ?string
    {
        return $this->_scope->getValue(
            self::CONFIG_PATH . '/forms/timestamps/cipher_iv',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string|null
     */
    public function getTimestampPassphrase(): ?string
    {
        return $this->_scope->getValue(
            self::CONFIG_PATH . '/forms/timestamps/cipher_passphrase',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getTimestampFieldName(): string
    {
        return $this->_scope->getValue(
            self::CONFIG_PATH . '/forms/timestamps/fieldname',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getRegex(): array
    {
        $result = [];

        $fields = $this->_scope->getValue(
            self::CONFIG_PATH . '/regex/expressions',
            ScopeInterface::SCOPE_STORE
        );

        if ($fields) {
            $fields = @json_decode($fields, true);

            if ($fields) {
                foreach ($fields as $field) {
                    $result[$field['name']] = $field['expression'];
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getExcludeFromRegex(): array
    {
        $result = [];

        $fields = $this->_scope->getValue(
            self::CONFIG_PATH . '/regex/exclude',
            ScopeInterface::SCOPE_STORE
        );

        if ($fields) {
            $fields = @json_decode($fields, true);

            if ($fields) {
                foreach ($fields as $field) {
                    $result[] = $field['field'];
                }
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function checkBlacklistedMailDomains(): bool
    {
        return (bool) $this->_scope->getValue(
            self::CONFIG_PATH . '/email/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getNoRouteUrl(): string
    {
        return $this->_scope->getValue(
            'web/default/no_route',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getAddressConfig(): array
    {
        $result = [];

        $fields = [
            'firstname',
            'lastname',
            'middlename',
            'company',
            'street',
            'city',
            'postcode',
            'telephone'
        ];

        foreach ($fields as $field) {
            $result[$field] = $this->_scope->getValue(
                self::CONFIG_PATH . '/address/' . $field,
                ScopeInterface::SCOPE_STORE
            );
        }

        return $result;
    }

    /**
     * @return array|null
     */
    public function getFormTimestampActions(): ?array
    {
        if ($this->_formTimestampActions === null) {
            $this->_formTimestampActions = [];

            $actions = $this->_scope->getValue(
                self::CONFIG_PATH . '/forms/timestamps/forms',
                ScopeInterface::SCOPE_STORE
            );

            if ($actions) {
                $actions = @json_decode($actions, true);

                if ($actions) {
                    foreach ($actions as $row) {
                        $this->_formTimestampActions[] = trim($row['action']);
                    }
                }
            }
        }

        return $this->_formTimestampActions;
    }

    /**
     * @return array|null
     */
    public function getFormHoneypotActions(): ?array
    {
        if ($this->_formHoneypotActions === null) {
            $this->_formHoneypotActions = [];

            $actions = $this->_scope->getValue(
                self::CONFIG_PATH . '/forms/honeypots/forms',
                ScopeInterface::SCOPE_STORE
            );

            if ($actions) {
                $actions = @json_decode($actions, true);

                if ($actions) {
                    foreach ($actions as $row) {
                        $this->_formHoneypotActions[] = [
                            'name' => trim($row['name']),
                            'action' => trim($row['action'])
                        ];
                    }
                }
            }
        }

        return $this->_formHoneypotActions;
    }

    /**
     * @return array
     */
    public function getFormHoneypotData(): array
    {
        if ($this->_honeypotData === null) {
            $this->_honeypotData = [];

            if ($actions = $this->getFormHoneypotActions()) {
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
     * @param string $formAction
     * @return string
     */
    public function getFieldnameByFormAction(string $formAction): string
    {
        $result = '';
        $actions = $this->getFormHoneypotData();

        if (is_array($actions)) {
            $result = isset($actions[$formAction]) ? $actions[$formAction]['name'] : '';
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getMaxBlacklistEntries(): int
    {
        return (int) $this->_scope->getValue(
            self::CONFIG_PATH . '/misc/max_blacklist_entries',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getLogLifetime(): int
    {
        return (int) $this->_scope->getValue(
            self::CONFIG_PATH . '/misc/log_lifetime_days',
            ScopeInterface::SCOPE_STORE
        );
    }
}