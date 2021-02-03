<?php

require '../vendor/autoload.php';
require 'settings.php';

ini_set('memory_limit','256M');

$cache = new \Symfony\Component\Cache\Adapter\FilesystemAdapter('demo', $defaultLifetime = 0,  $directory = 'cache');
$psr16Cache = new \Symfony\Component\Cache\Psr16Cache($cache);

$tuffnells = new Tuffnells\Application(TUFFNELLS_ACCOUNT_ID, TUFFNELLS_USERNAME, TUFFNELLS_PASSWORD);
$logger = new SimpleLog\Logger('php://stderr', 'verbose');
$tuffnells->setLogger($logger);
$tuffnells->setCache($psr16Cache);
$consignment = $tuffnells->getConsignment($argv[1]);
$consignment->updateTracking();

if($consignment->getStatus() === \Tuffnells\Models\Consignment::STATUS_DELIVERED)
    print_r($consignment->getSignatures());
else
    echo $consignment->getStatus();