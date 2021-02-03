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

$collectionAddress = new Tuffnells\Models\Address($tuffnells);
$collectionAddress->setAddressLine1('9 Hagmill Road')
    ->setAddressLine2('East Shawhead Industrial Estate')
    ->setPostcode('ML5 4XD')
    ->setCompany('Matic Media Services Ltd')
    ->setContactName('Robert McCombe')
    ->setContactPhone('08442092274');

$deliveryAddress = new Tuffnells\Models\Address($tuffnells);
$deliveryAddress->setAddressLine1('9 Hagmill Road')
    ->setAddressLine2('East Shawhead Industrial Estate')
    ->setPostcode('ML5 4XD')
    ->setCompany('Matic Media Services Ltd')
    ->setContactName('Robert McCombe')
    ->setContactPhone('08442092274')
    ->setInstructions('Test');

$package = new Tuffnells\Models\Package();
$package->setQuantity(1)
    ->setType(Tuffnells\Models\Package::PACKAGE_CARTON)
    ->setWeight(20);

echo "Creating Consignment\n";
$consignment = new Tuffnells\Models\Consignment($tuffnells);
$consignment->setCollectionAddress($collectionAddress)
            ->setDeliveryAddress($deliveryAddress)
            ->setPackage1($package)
            ->setTuffnellsReference('Tuff Ref')
            ->setCustomerReference('Cus Ref')
            ->setConsignmentNumber('AB123456')
    ->save();

echo "Consignment Created - " . $consignment->getUrn();

$consignment->getDeliveryAddress()->setContactName('Bobby');
$consignment->save();

echo "Consignment Updated";

$consignment->delete();

echo "Consignment Deleted - " . $consignment->getUrn();