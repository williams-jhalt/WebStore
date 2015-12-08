<?php

namespace AppBundle\Command;

use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ErpOneOrderSyncCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:erpone:order_sync')
                ->setDescription('Refresh open orders and load new records');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $service = $this->getContainer()->get('app.order_sync_service');
        $output->write("Beginning erp order refresh...\n");
        try {
            $service->updateOpenOrders($output);
            $service->loadNewOrders($output);
            $service->loadConsolidatedInvoices($output);
        } catch (Exception $e) {
            $output->writeln("There was an error refreshing the order status: " . $e->getMessage());
        }
        $output->write("Finished!\n\n");
    }

}
