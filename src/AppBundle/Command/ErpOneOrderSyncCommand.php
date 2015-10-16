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

        $service2 = $this->getContainer()->get('app.order_service2');
        $output->write("Beginning erp order refresh...\n");
        try {
            $service2->loadFromErp($output);
        } catch (Exception $e) {
            echo $e;
            $output->writeln("There was an error refreshing the order status");
        }
        $output->write("Finished!\n\n");
    }

}
