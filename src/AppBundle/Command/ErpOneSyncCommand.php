<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ErpOneSyncCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:erpone:load')
                ->setDescription('Load all items from ERP-ONE');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $service2 = $this->getContainer()->get('app.weborder_service');
        $output->write("Beginning weborder load...\n");
        $service2->batchUpdate($output);
        $output->write("Finished!\n\n");
        $service = $this->getContainer()->get('app.product_service');
        $output->write("Beginning product load...\n");
        $service->loadAllFromErp($output);
        $output->write("Finished!\n\n");
    }

}
