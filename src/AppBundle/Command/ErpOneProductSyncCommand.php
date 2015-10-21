<?php

namespace AppBundle\Command;

use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ErpOneProductSyncCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:erpone:product_sync')
                ->setDescription('Refresh open orders and load new records');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $service = $this->getContainer()->get('app.product_sync_service');
        $output->write("Beginning erp product refresh...\n");
        try {
            $service->loadFromErp($output);
        } catch (Exception $e) {
            echo $e;
            $output->writeln("There was an error refreshing the product status");
        }
        $output->write("Finished!\n\n");
        
    }

}
