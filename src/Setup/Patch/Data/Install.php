<?php
declare(strict_types=1);
namespace OM\Nospam\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir;
use OM\Nospam\Model\Config;

class Install implements DataPatchInterface
{
    const CSV_FILE = 'trashmaildomains.csv';

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected WriterInterface $_configWriter;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected File $_file;

    /**
     * @var \Magento\Framework\Module\Dir
     */
    protected Dir $_dir;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected AdapterInterface $_db;

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $_moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Module\Dir $dir
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter,
        \Magento\Framework\App\ResourceConnection $connection,
        File $file,
        Dir $dir
    ) {
        $this->_moduleDataSetup = $moduleDataSetup;
        $this->_configWriter = $configWriter;
        $this->_file = $file;
        $this->_dir = $dir;
        $this->_db = $connection->getConnection('default');
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->_setupMailDomains();
        $this->_setupConfigData();
    }

    /**
     * @return void
     * @throws \Random\RandomException
     */
    protected function _setupConfigData()
    {
        $path = Config::CONFIG_PATH . '/forms/timestamps/';
        $ivlen = openssl_cipher_iv_length('aes-128-cbc');
        $char = chr(ord('a') + rand(0, 25));
        $this->_configWriter->save($path . 'fieldname', $char . bin2hex(random_bytes(4)));
        $this->_configWriter->save($path . 'cipher_iv', bin2hex(random_bytes($ivlen / 2)));
        $this->_configWriter->save($path . 'cipher_passphrase', bin2hex(random_bytes(32)));
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

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}