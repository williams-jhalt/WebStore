<?php

// load RDS variables if they exist

if(@$_ENV['RDS_HOSTNAME']) {
    $container->setParameter('database_host', $_ENV['RDS_HOSTNAME']);
    $container->setParameter('database_port', $_ENV['RDS_PORT']);
    $container->setParameter('database_name', $_ENV['RDS_DB_NAME']);
    $container->setParameter('database_user', $_ENV['RDS_USERNAME']);
    $container->setParameter('database_password', $_ENV['RDS_PASSWORD']);
}

if (@$_ENV['ERP_SERVER']) {
    $container->setParameter('erp_server', $_ENV['ERP_SERVER']);
    $container->setParameter('erp_username', $_ENV['ERP_USERNAME']);
    $container->setParameter('erp_password', $_ENV['ERP_PASSWORD']);
    $container->setParameter('erp_company', $_ENV['ERP_COMPANY']);
    $container->setParameter('erp_appname', $_ENV['ERP_APPNAME']);
}

if (@$_ENV['CSRF_SECRET']) {
    $container->setParameter('secret', $_ENV['CSRF_SECRET']);
}
