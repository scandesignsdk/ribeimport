<?php
namespace Ribe\Import\Command;

require_once __DIR__ . '/../../Settings.php';

use Datapump\Product\ItemHolder;
use Intervention\Image\ImageManager;
use Ribe\Import\Import;
use Ribe\Import\Logger\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CreateCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('ribe:import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settings = new \Settings();

        require $settings->getMagmiDir() . '/inc/magmi_defs.php';
        require $settings->getMagmiDir() . '/integration/inc/magmi_datapump.php';

        $itemholder = new ItemHolder(new Logger('ribe_import'));
        $itemholder->setMagmi(\Magmi_DataPumpFactory::getDataPumpInstance("productimport"), 'default');

        $importer = new Import($itemholder, new Finder(), new Filesystem(), $settings);
        $importer->import($output);

    }

}