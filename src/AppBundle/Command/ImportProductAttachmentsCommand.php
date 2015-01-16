<?php

namespace AppBundle\Command;

use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportProductAttachmentsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:import:productattachments')
                ->setDescription('Import Product Attachments')
                ->addArgument(
                        'file', InputArgument::REQUIRED, 'File to import'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $filename = $input->getArgument('file');

        $file = new SplFileObject($filename, "r");

        $service = $this->getContainer()->get('app.product_attachment_service');

        $service->importFromCSV($file, array(
            'sku' => 0,
            'filename' => 2
                ), true);
    }

}
