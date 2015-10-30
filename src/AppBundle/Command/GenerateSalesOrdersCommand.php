<?php

namespace AppBundle\Command;

use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSalesOrdersCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:generate_sales_orders')
                ->setDescription('Generate sales orders from erp data');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $service2 = $this->getContainer()->get('app.order_sync_service');
        $output->write("Beginning sales order generation...\n");
        try {
            $service2->generateSalesOrders($output);
        } catch (Exception $e) {
            echo $e;
            $output->writeln("There was an error refreshing the order status");
        }
        $output->write("Finished!\n\n");
    }

}
