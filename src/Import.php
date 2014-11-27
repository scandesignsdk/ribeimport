<?php
namespace Ribe\Import;

use Datapump\Product\Configurable;
use Datapump\Product\Data\Category;
use Datapump\Product\Data\RequiredData;
use Datapump\Product\ItemHolder;
use Datapump\Product\Simple;
use Monolog\Logger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Import
{

    private $debug = false;

    private $csvFileDir = '';

    private $csvBackupDir = '';

   /**
     * @var Finder
     */
    private $finder;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var array
     */
    private $configurableProducts;

    /**
     * @var ItemHolder
     */
    private $itemHolder;

    /**
     * @var \Settings
     */
    private $settings;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param ItemHolder $itemHolder
     * @param Finder $finder
     * @param Filesystem $fs
     * @param \Settings $settings
     */
    public function __construct(
        ItemHolder $itemHolder,
        Finder $finder,
        Filesystem $fs,
        \Settings $settings
    ) {
        $this->finder = $finder;
        $this->fs = $fs;
        $this->itemHolder = $itemHolder;
        $this->settings = $settings;
        $this->logger = new Logger('ribe');
    }

    public function import(OutputInterface $output)
    {
        $this->output = $output;
        foreach($this->getDataFiles() as $file) {
            if ($this->logger->getHandlers()) {
                $this->logger->popHandler();
            }

            $resource = fopen($file, 'r');
            $i = 0;
            while (($data = fgetcsv($resource, null, $this->settings->getCsvData('demiliter'), $this->settings->getCsvData('enclosure'))) !== FALSE) {
                if ($this->settings->getCsvData('hasHeaders') && ++$i <= 1) {
                    continue;
                }

                $data = array_combine($this->settings->getCsvData('headers'), $data);
                if ($data['type'] == 'config') {
                    $this->createConfigurableProduct($data);
                } else {
                    $this->createSimpleProduct($data);
                }
            }
            fclose($resource);

            foreach($this->configurableProducts as $config) {
                $this->itemHolder->addProduct($config);
            }

            $this->doImport();
            $this->output->writeln('<info>File "' . $file->getBasename() . '" imported</info>');
            $this->fs->rename($file, $this->csvBackupDir . $file->getBasename());
        }
    }

    private function createSimpleProduct(array $data)
    {
        $simpleData = new RequiredData();
        $simpleData
            ->setSku($data['sku'])
            ->setName($data['name'])
            ->setDescription($data['long_description'])
            ->setShortDescription($data['short_description'])
            ->setWeight($data['weight'])
            ->setPrice($this->convertPrice($data['price']))
            ->setTax('Taxable Goods')
            ->setQty(9999, true)
        ;

        foreach($this->getVariants($data) as $v) {
            $simpleData->set($v, $data[$v]);
        }

        $simpleProduct = new Simple($simpleData);

        if (isset($this->configurableProducts[$data['related']])) {
            /** @var Configurable $configProduct */
            $configProduct = $this->configurableProducts[$data['related']];
            $configProduct->addSimpleProduct($simpleProduct);
            $this->logger->log('debug', 'Simple product SKU ' . $data['sku'] . ' ready to import');
        } else {
            $this->logger->log('alert', 'Related configurable SKU ' . $data['related'] . ' was not found');
        }
    }

    private function createConfigurableProduct(array $data)
    {
        $configdata = new RequiredData();
        $configdata
            ->setName($data['name'])
            ->setSku($data['sku'])
            ->setDescription($data['long_description'])
            ->setShortDescription($data['short_description'])
            ->setQty(9999, true)
            ->setPrice($this->convertPrice($data['price']))
        ;

        $configProduct = new Configurable($configdata, $this->getVariants($data));
        $configProduct->injectData($this->getCategories($data));

        $this->configurableProducts[$configdata->getSku()] = $configProduct;

        $this->logger->log('debug', 'Configurable SKU ' . $data['sku'] . ' ready to import');
    }

    /**
     * @param array $data
     * @return Category
     */
    private function getCategories(array $data)
    {
        $categories = array();
        if (strpos($data['category'], ',') !== false) {
            $categories = explode(',', $data['category']);
        } else {
            $categories[] = $data['category'];
        }
        $categories = array_map('trim', $categories);

        $cat = new Category();
        $cat->set(implode(Category::DEFAULT_CATEGORY_SEPARATOR, $categories));
        return $cat;
    }

    /**
     * @param string $price
     * @return float
     */
    private function convertPrice($price)
    {
        return (float)str_replace(',', '', $price);
    }

    /**
     * @param array $data
     * @return array
     */
    private function getVariants(array $data)
    {
        $variants = array();
        if (strpos($data['variantlist'], ',') !== false) {
            $variants = explode(',', $data['variantlist']);
        } else {
            $variants[] = $data['variantlist'];
        }
        return array_map($variants, 'trim');
    }

    private function doImport()
    {
        if ($this->debug) {
            var_dump($this->itemHolder->import(true));
        } else {
            $this->itemHolder->import();
        }
    }

    /**
     * @return SplFileInfo[]
     */
    private function getDataFiles()
    {
        $finder = clone $this->finder;
        $files = $finder->files()->in($this->csvFileDir)->depth(0)->name('*.csv');
        return $files;
    }

}
 