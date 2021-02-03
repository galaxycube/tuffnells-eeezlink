<?php

require '../vendor/autoload.php';
require 'settings.php';

$cache = new \Symfony\Component\Cache\Adapter\FilesystemAdapter('demo', $defaultLifetime = 0,  $directory = 'cache');
$psr16Cache = new \Symfony\Component\Cache\Psr16Cache($cache);

$tuffnells = new Tuffnells\Application(TUFFNELLS_ACCOUNT_ID, TUFFNELLS_USERNAME, TUFFNELLS_PASSWORD);
$logger = new SimpleLog\Logger('php://stderr', 'verbose');
$tuffnells->setLogger($logger);
$tuffnells->setCache($psr16Cache);

$consignment = new \Tuffnells\Models\Consignment($tuffnells, $argv[1]);

$handle = fopen('label.png','wb+');
fwrite($handle, $consignment->getLabels()->getPng());
fclose($handle);

$handle = fopen('label.pdf','wb+');
fwrite($handle, $consignment->getLabels()->getPdf());
fclose($handle);

echo $consignment->getLabels()->getZpl();