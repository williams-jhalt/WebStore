<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Exception\Exception;

class ExportCatalogCsvCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:export:csv')
                ->setDescription('Export Catalog as CSV')
                ->addArgument('table', InputArgument::REQUIRED, 'Table to Export (product,category,manufacturer,product_attachment,product_type')
                ->addArgument('output', InputArgument::REQUIRED, 'File to export')
                ->addArgument('format', InputArgument::OPTIONAL, 'Product file format (default,short,categories,release_date,minimal,dimensions)', 'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $filename = $input->getArgument('output');
        $table = $input->getArgument('table');
        $format = $input->getArgument('format');

        switch ($table) {

            case "product":
                $service = $this->getContainer()->get('app.product_service');
                $service->exportCsv($filename, $format);
                break;

            case "category":
                $service = $this->getContainer()->get('app.category_service');
                $service->exportCsv($filename);
                break;

            case "manufacturer":
                $service = $this->getContainer()->get('app.manufacturer_service');
                $service->exportCsv($filename);
                break;

            case "product_attachment":
                $service = $this->getContainer()->get('app.product_attachment_service');
                $service->exportCsv($filename);
                break;

            case "product_type":
                $service = $this->getContainer()->get('app.product_type_service');
                $service->exportCsv($filename);
                break;

            default:
                throw new Exception("Invalid table specified");
        }
    }

}
