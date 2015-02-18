<?php

namespace AppBundle\Command;

use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportProductDetailsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:import:product_details')
                ->setDescription('Import Product Details')
                ->addArgument(
                        'file', InputArgument::REQUIRED, 'File to import'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $filename = $input->getArgument('file');

        $file = new SplFileObject($filename, "r");

        $service = $this->getContainer()->get('app.product_service');

        $service->importFromCSV($file, array(
            'sku' => 0,
            'name' => 1,
            'releaseDate' => 2,
            'stockQuantity' => 3,
            'manufacturerCode' => 4,
            'productTypeCode' => 5,
            'categoryCodes' => 6,
            'barcode' => 7
                ), true);
    }

}
