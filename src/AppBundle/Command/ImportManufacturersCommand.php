<?php

namespace AppBundle\Command;

use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportManufacturersCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:import:manufacturers')
                ->setDescription('Import Manufacturers')
                ->addArgument(
                        'file', InputArgument::REQUIRED, 'File to import'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $filename = $input->getArgument('file');

        $file = new SplFileObject($filename, "r");

        $service = $this->getContainer()->get('app.manufacturer_service');

        $service->importFromCSV($file, array(
            'code' => 0,
            'name' => 1
                ), true);
    }

}
