<?php

namespace AppBundle\Command;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ErpOneSyncCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:erpone:load')
                ->setDescription('Load all items from ERP-ONE');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        $startDate = new DateTime();
        $startDate->modify("-1 day");
        $endDate = new DateTime();
        
        $service2 = $this->getContainer()->get('app.erp_order_service');
        $output->write("Beginning erp order load...\n");
        $service2->loadFromErpOne($startDate, $endDate, $output);
        $output->write("Finished!\n\n");
    }

}
