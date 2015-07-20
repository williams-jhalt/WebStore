<?php

// load RDS variables if they exist

if(@$_ENV['RDS_HOSTNAME']) {
    $container->setParameter('database_host', $_ENV['RDS_HOSTNAME']);
    $container->setParameter('database_port', $_ENV['RDS_PORT']);
    $container->setParameter('database_name', $_ENV['RDS_DB_NAME']);
    $container->setParameter('database_user', $_ENV['RDS_USERNAME']);
    $container->setParameter('database_password', $_ENV['RDS_PASSWORD']);
}
