<?php

namespace Setup;

use Magento\Framework\Module\Dir;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    const CSV_FILE = 'trashmaildomains.csv';

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected \Magento\Framework\App\Config\Storage\WriterInterface $_configWriter;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected \Magento\Framework\Filesystem\Io\File $_file;

    /**
     * @var \Magento\Framework\Module\Dir
     */
    protected \Magento\Framework\Module\Dir $_dir;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected \Magento\Framework\DB\Adapter\AdapterInterface $_db;

    /**
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Module\Dir $dir
     */
    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Module\Dir $dir
    ) {
        $this->_configWriter = $configWriter;
        $this->_file = $file;
        $this->_dir = $dir;
        $this->_db = $connection->getConnection('default');
    }

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     * @return void
     * @throws \Exception
     */
    public function install(\Magento\Framework\Setup\ModuleDataSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $path = \Model\Config::CONFIG_PATH . '/forms/timestamps/';
        $ivlen = openssl_cipher_iv_length('aes-128-cbc');
        $char = chr(ord('a') + rand(0, 25));
        $this->_configWriter->save($path . 'fieldname', $char . bin2hex(random_bytes(4)));
        $this->_configWriter->save($path . 'cipher_iv', bin2hex(random_bytes($ivlen / 2)));
        $this->_configWriter->save($path . 'cipher_passphrase', bin2hex(random_bytes(32)));
        $this->_setupMailDomains();
    }

    /**
     * @return void
     */
    protected function _setupMailDomains()
    {
        $dir = $this->_dir->getDir('OM_Nospam', Dir::MODULE_ETC_DIR);
        $file = $dir . '/' . self::CSV_FILE;

        if (file_exists($file)) {
            $data = $this->_file->read($file);

            if ($data) {
                $data = str_replace("\r", '', $data);
                $data = explode("\n", $data);

                foreach ($data as $domain) {
                    $domain = trim($domain);
                    if (!empty($domain)) {
                        $this->_db->query(
                            "INSERT IGNORE om_nospam_domains
                            SET `domain` = :domain", [
                                'domain' => $domain
                            ]
                        );
                    }
                }
            }
        }
    }
}
