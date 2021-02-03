<?php
/**
 * gets a city based off a postcode from the tuffnells database
 */

require '../vendor/autoload.php';
require 'settings.php';

$cache = new \Symfony\Component\Cache\Adapter\FilesystemAdapter('demo', $defaultLifetime = 0,  $directory = 'cache');
$psr16Cache = new \Symfony\Component\Cache\Psr16Cache($cache);

$tuffnells = new Tuffnells\Application(TUFFNELLS_ACCOUNT_ID, TUFFNELLS_USERNAME, TUFFNELLS_PASSWORD);
$logger = new SimpleLog\Logger('php://stderr', 'verbose');
$tuffnells->setLogger($logger);
$tuffnells->setCache($psr16Cache);

print_r($tuffnells->getCityRegion('ML6 9HQ'));
print_r($tuffnells->getCityRegion('ML5 4xd'));
print_r($tuffnells->getCityRegion('DE5 3AX'));
