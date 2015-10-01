<?php

namespace AppBundle\Command;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ErpOneSyncCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:erpone:refresh')
                ->setDescription('Refresh open orders and load new records');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        $service = $this->getContainer()->get('app.order_service');
        $output->write("Beginning erp order refresh...\n");
        $service->refreshOrders($output);
        $output->write("Finished!\n\n");
        
    }

}
