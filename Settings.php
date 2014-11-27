<?php
// Remember to check magmi/conf/magmi.ini for magmi database settings
// Remember trailing slash on directories
// All directories are relative to THIS file

class Settings
{

    public $debug = false;

    public $magento_rootdir = '/../';

    public $magmi_dir = '/magmi/';

    public $files_dir = array(
        'csvfiles' => '/data/',
        'csv_backup' => '/data/imported/',
    );

    public $csv = array(
        'hasHeaders' => true,
        'demiliter' => ';',
        'enclosure' => '"',
        'headers' => array('type', 'related', 'sku', 'name', 'variantlist', 'farve', 'str', 'tmp', 'short_description', 'long_description', 'weight', 'price', 'special_price', 'category', 'tmp')
    );

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @return string
     */
    public function getMagmiDir()
    {
        return __DIR__ . $this->magmi_dir;
    }

    /**
     * @return string
     */
    public function getMagentoDir()
    {
        return __DIR__ . $this->magento_rootdir;
    }

    /**
     * @param $type
     * @throws Exception
     * @return string
     */
    public function getDir($type)
    {
        if (array_key_exists($type, $this->files_dir)) {
            return __DIR__ . $this->files_dir[$type];
        }

        throw new \Exception('Directory type ' . $type . ' does not exists');

    }

    /**
     * @param $type
     * @throws Exception
     * @return string
     */
    public function getCsvData($type)
    {
        if (array_key_exists($type, $this->csv)) {
            return $this->csv[$type];
        }

        throw new \Exception('CsvData type ' . $type . ' does not exists');
    }

}