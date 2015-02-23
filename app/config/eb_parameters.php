<?php

$container->setParameter('database_driver', 'pdo_mysql');
$container->setParameter('database_host', getenv('RDS_HOSTNAME'));
$container->setParameter('database_port', getenv('RDS_PORT'));
$container->setParameter('database_name', getenv('RDS_DB_NAME'));
$container->setParameter('database_user', getenv('RDS_USERNAME'));
$container->setParameter('database_password', getenv('RDS_PASSWORD'));