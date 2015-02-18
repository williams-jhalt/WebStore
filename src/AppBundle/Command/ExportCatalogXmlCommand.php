<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCatalogXmlCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:export:xml')
                ->setDescription('Export Catalog as XML')
                ->addArgument('output', InputArgument::REQUIRED, 'File to export');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $filename = $input->getArgument('output');

        $service = $this->getContainer()->get('app.product_service');

        $service->exportToXML($filename);
    }

}
