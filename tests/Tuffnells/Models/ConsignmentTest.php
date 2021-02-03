<?php

namespace Tuffnells\Models;

use PHPUnit\Framework\TestCase;
use Tuffnells\Application;
use Tuffnells\Exceptions\ConsignmentNotFound;
use Tuffnells\Exceptions\InvalidConsignment;
use Tuffnells\Exceptions\InvalidDispatchDate;
use Tuffnells\Models\Consignment\History;
use Tuffnells\Models\Consignment\History\Log;
use Tuffnells\Models\Consignment\Label;
use Tuffnells\Models\Consignment\Signatures;

class ConsignmentTest extends TestCase
{
    private Consignment $_consignment;

    public function setUp(): void {

        $mock = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('trackConsignment')->willReturnCallback(
            function(Consignment $consignment) : Consignment {
                $consignment->getUrn();
                return $consignment;
            }
        );

        $mock->method('getLabels')->willReturnCallback(
            function(Consignment $consignment) : Label {
                $urn = $consignment->getUrn();
                if($urn === 'NOTFOUND')
                    throw new ConsignmentNotFound();
                return new Label($consignment, 'ZPL');
            }
        );

        $mock->method('createConsignment')->willReturnCallback(
            function(Consignment $consignment) : Consignment {
                $consignment->setURN('SAVED');
                return $consignment;
            }
        );

        $mock->method('amendConsignment')->willReturnCallback(
            function(Consignment $consignment) : Consignment {
                $consignment->setURN('UPDATED');
                return $consignment;
            }
        );

        $mock->method('deleteConsignment')->willReturn(true);

        $this->_consignment = new Consignment($mock);
    }

    /**
     * @testCase Test Get/Set Status & default status
     */
    public function testGetStatus(): void {
        self::assertEquals(Consignment::STATUS_AWAITING_PICKUP, $this->_consignment->getStatus()); //default status should always be awaiting pickup
        $this->_consignment->setStatus(Consignment::STATUS_DELIVERED);
        self::assertEquals(Consignment::STATUS_DELIVERED, $this->_consignment->getStatus());
    }

    /**
     * @testCase Set invalid consignment type
     * @throws InvalidConsignment
     */
    public function testSetStatus(): void {
        $this->expectException(InvalidConsignment::class);
        $this->_consignment->setStatus(600); //lets check status cannot be set to something unregistered

        $this->expectException(InvalidConsignment::class);
        $this->_consignment->setStatus(-3); //lets check status cannot be set to something unregistered
    }

    /**
     * @testCase Checks URN can be set correctly
     * @throws InvalidConsignment
     */
    public function testSetURN()
    {
        $urn = 'TEST';
        $this->_consignment->setURN($urn);
        self::assertEquals($urn, $this->_consignment->getUrn());
    }

    /**
     * @testCase Checks urn cannot be returned until set
     * @throws InvalidConsignment
     */
    public function testGetEmptyURN()
    {
        $this->expectException(InvalidConsignment::class);
        $this->_consignment->getUrn();
    }

    /**
     * @testCase Checks urn cannot be set when empty
     * @throws InvalidConsignment
     */
    public function testSetEmptyURN()
    {
        $this->expectException(InvalidConsignment::class);
        $urn = '';
        $this->_consignment->setURN($urn);
        $this->_consignment->getUrn();
    }

    /**
     * @testCase Setting a valid consignment number
     */
    public function testSetValidConsignmentNumber(): void {
        $consignmentNumber = 'CONSIGNMENT';
        $this->_consignment->setConsignmentNumber($consignmentNumber);
        self::assertEquals($this->_consignment->getConsignmentNumber(), $consignmentNumber);
    }

    /**
     * @testCase Sets an invalid consignment number
     */
    public function testGetInvalidConsignmentNumber(): void {
        $this->expectException(InvalidConsignment::class);
        $this->_consignment->getConsignmentNumber();
    }

    /**
     * @testCase Sets an invalid consignment number
     */
    public function testSetInvalidConsignmentNumber(): void {
        $this->expectException(InvalidConsignment::class);
        $this->_consignment->setConsignmentNumber('');
        $this->_consignment->setConsignmentNumber(0);
        $this->_consignment->setConsignmentNumber(null);
    }

    /**
     * @testCase Check Getter and Setter methods for Package Types
     */
    public function testSetPackages(): void {
        for($i=1; $i<=3; $i++) {
            $package = $this->prophesize(Package::class);
            $method = 'setPackage' . $i;
            $this->_consignment->$method($package->reveal());
            $method = 'getPackage' . $i;
            self::assertInstanceOf(Package::class, $this->_consignment->$method());
        }
    }

    /**
     * @testCase Set/Get collection address
     */
    public function testSetCollectionAddress(): void {
        $address = $this->prophesize(Address::class);
        $this->_consignment->setCollectionAddress($address->reveal());
        self::assertEquals($address->reveal(), $this->_consignment->getCollectionAddress());
    }

    /**
     * @testCase Set/Get delivery address
     */
    public function testSetDeliveryAddress(): void {
        $address = $this->prophesize(Address::class);
        $this->_consignment->setDeliveryAddress($address->reveal());
        self::assertEquals($address->reveal(), $this->_consignment->getDeliveryAddress());
    }

    /**
     * @testCase Set/Get Tuffnells Reference
     */
    public function testGetSetTuffnellsReference(): void {
        $reference = 'Reference';
        $this->_consignment->setTuffnellsReference($reference);
        self::assertEquals($reference, $this->_consignment->getTuffnellsReference());
    }

    /**
     * @testCase Set/Get Customer Reference
     */
    public function testGetSetCustomerReference(): void {
        $reference = 'Customer';
        $this->_consignment->setCustomerReference($reference);
        self::assertEquals($reference, $this->_consignment->getCustomerReference());
    }

    /**
     * @testCase Tries setting an invalid dispatch date in the past
     * @throws InvalidDispatchDate
     */
    public function testSetInvalidDispatchDate(): void {
        $this->expectException(InvalidDispatchDate::class);
        $date = new \DateTime();
        $date->modify('-3 day'); //Set to 3 days in past
        $this->_consignment->setDispatchDate($date);
    }

    /**
     * @testCase Check if a future dated date time is valid
     * @throws InvalidDispatchDate
     */
    public function testSetValidDispatchDate(): void {
        $future_date = new \DateTime();
        $future_date->modify('3 days');
        $this->_consignment->setDispatchDate($future_date);
        self::assertEquals($future_date, $this->_consignment->getDispatchDate());
    }

    private function createValidConsignment(): Consignment {
        $application = $this->prophesize(Application::class)->reveal();
        $consignment = new Consignment($application);

        //set addresses
        $address = new Address($application);
        $address->setAddressLine1('ABC 1');
        $address->setAddressLine2('ABC 2');
        $address->setPostcode('ML6 9HB', new CityRegion('AIRDRIE', 'LANARKSHIRE'));
        $address->setContactName('Test McTest');
        $consignment->setDeliveryAddress($address);
        $consignment->setCollectionAddress($address);
        $consignment->setStatus(Consignment::STATUS_DELIVERED);

        //set package
        $consignment->getPackage1()->setWeight(20)->setQuantity(1);

        //create signatures
        $signature = new Signatures\Signature();
        $signature->setDatetime(new \DateTime());
        $signature->setSignature('Test McTest');
        $signatures = new Signatures();
        $signatures->add($signature);
        $consignment->setSignatures($signatures);

        //create logs
        $log = new Log();
        $log->setPackagesDelivered(1);
        $log->setPackagesReceived(1);
        $log->setRoundNumber('11');
        $log->setDeliveryDepot('ABC');
        $log->setDescription('Delivered');
        $log->setDate(new \DateTime());
        $history = new History();
        $history->add($log);
        $consignment->setLogs($history);

        //don't set consignment number
        //don't set URN

        return $consignment;

    }

    public function testValidConsignment(): void{
        $consignment = $this->createValidConsignment();
        $consignment->setConsignmentNumber('ABC123');
        self::assertEquals(true,$consignment->isValid());
    }

    public function testInvalidCollectionAddress(): void {
        $consignment = $this->createValidConsignment();
        $consignment->setConsignmentNumber('ABC123');
        $consignment->setCollectionAddress(new Address($this->prophesize(Application::class)->reveal())); //blank address
        $this->expectException(InvalidConsignment::class);
        $consignment->isValid();
    }

    public function testInvalidDeliveryAddress(): void {
        $consignment = $this->createValidConsignment();
        $consignment->setConsignmentNumber('ABC123');
        $consignment->setDeliveryAddress(new Address($this->prophesize(Application::class)->reveal())); //blank address
        $this->expectException(InvalidConsignment::class);
        $consignment->isValid();
    }

    public function testInvalidPackages(): void {
        $consignment = $this->createValidConsignment();
        $consignment->setPackage1(new Package());
        $this->expectException(InvalidConsignment::class);
        $consignment->isValid();
    }

    public function testInvalidConsignmentNumber(): void {
        $consignment = $this->createValidConsignment();
        $this->expectException(InvalidConsignment::class);
        $consignment->isValid();
    }

    public function testSerializeEmptyConsignment(): void {
        $application = $this->prophesize(Application::class)->reveal();
        $consignment = new Consignment($application); //create empty consignment
        $serialized = $consignment->serialize(); //serialize empty consignment
        self::assertIsString($serialized); //check it returned a valid string

        $this->_consignment->setApplication($application); //apply application placeholder to class consignment
        $this->_consignment->unserialize($serialized); //reload data

        self::assertEquals($this->_consignment, $consignment); //check if both are equal
    }

    public function testSerializeFullConsignment(): void {
        $application = $this->prophesize(Application::class)->reveal(); //placeholder application

        $consignment = $this->createValidConsignment();
        $consignment->setConsignmentNumber('ABCTEST');
        $consignment->setURN('Test');
        $consignment->setApplication($application); //set the same application for testing

        $serialized = $consignment->serialize();
        self::assertIsString($serialized);
        $this->_consignment->unserialize($serialized);
        $this->_consignment->setApplication($application); //set the same application for testing

        self::assertEquals($this->_consignment, $consignment);
    }

    /**
     * @testCase Check that service type can be set from valid selection
     */
    public function testSetValidServiceType(): void {
        self::assertEquals(Consignment::SERVICE_TYPE_NEXTDAY,$this->_consignment->getServiceType());
        $this->_consignment->setServiceType(Consignment::SERVICE_TYPE_2DAY);
        self::assertEquals(Consignment::SERVICE_TYPE_2DAY,$this->_consignment->getServiceType());
    }

    /**
     * @testCase Checks invalid service types cannot be set
     */
    public function testSetInvalidServiceType(): void {
        $this->expectException(InvalidConsignment::class);
        $this->_consignment->setServiceType(-100);

        $this->expectException(InvalidConsignment::class);
        $this->_consignment->setServiceType(200);
    }

    /**
     * @testCase Logs should show as one on valid consignment
     * @throws InvalidConsignment
     */
    public function testGetLogs(): void {
        $logs = $this->createValidConsignment()->getLogs();
        self::assertCount(1, $logs);
    }

    /**
     * @testCase Check that an exception is thrown when there are no logs
     * @throws InvalidConsignment
     */
    public function testEmptyGetLogs(): void {
        $this->expectException(InvalidConsignment::class);
        $this->_consignment->getLogs(); //no logs set
    }

    /**
     * @testCase Signatures should show as one on valid consignment
     * @throws InvalidConsignment
     */
    public function testGetSignatures(): void {
        $signatures = $this->createValidConsignment()->getSignatures();
        self::assertCount(1, $signatures);
    }

    /**
     * @testCase Status set to not delivered shoudl return no signatures
     * @throws InvalidConsignment
     */
    public function testNotDeliveredGetSignatures(): void {
        $this->expectException(InvalidConsignment::class);
        $signatures = $this->createValidConsignment()->setStatus(Consignment::STATUS_IN_TRANSIT)->getSignatures();
    }


    /**
     * @testCase Check that an exception is thrown when there are no logs
     * @throws InvalidConsignment
     */
    public function testEmptyGetSignatures(): void {
        $this->expectException(InvalidConsignment::class);
        $this->_consignment->getSignatures(); //no logs set
    }

    /**
     * @testCase Checks the average parcel weight returned is correct
     * @throws \Tuffnells\Exceptions\InvalidPackageQuantity
     */
    public function testGetAverageParcelWeight(): void {
        self::assertEquals(0,$this->_consignment->getAveragePackageWeight());

        $package = new Package();
        $package->setQuantity(2);
        $package->setWeight(14);
        $this->_consignment->setPackage1($package);

        $package = new Package();
        $package->setQuantity(1);
        $package->setWeight(10);
        $this->_consignment->setPackage2($package);

        $package = new Package();
        $package->setQuantity(3);
        $package->setWeight(22);
        $this->_consignment->setPackage3($package);

        //((2*14) + (1*10) + (3*22))/(2+1+3) = 17.3333333
        self::assertEquals(18, $this->_consignment->getAveragePackageWeight());
    }

    /**
     * @testCase Invalid URN testing on consignment tracking
     */
    public function testUpdateTrackingInvalidUrn(): void {
        $this->expectException(InvalidConsignment::class);
        $this->_consignment->updateTracking();
    }

    /**
     * @testCase Tracks consignment with valid URN
     * @throws InvalidConsignment
     */
    public function testUpdateTrackingValidUrn(): void {
        $urn = 'TEST';
        $this->_consignment->setURN($urn);
        self::assertEquals($urn, $this->_consignment->updateTracking()->getUrn());
    }

    /**
     * @testCase No URN Set, get Labels
     */
    public function testGetLabelsInvalidURN(): void {
        $this->expectException(InvalidConsignment::class);
        $this->_consignment->getLabels();
    }

    /**
     * @testCase Invalid URN Set, get Labels
     */
    public function testGetLabelsNotFoundURN(): void {
        $this->expectException(ConsignmentNotFound::class);
        $this->_consignment->setURN('NOTFOUND');
        $this->_consignment->getLabels();
    }

    /**
     * @testCase Valid URN, get Labels
     * @throws InvalidConsignment
     */
    public function testGetLabelsValidURN(): void {
        $this->_consignment->setURN('VALID');
        self::assertInstanceOf(Label::class , $this->_consignment->getLabels());
    }

    /**
     * @testCase Saving and Updating a Consignment
     */
    public function testSave(): void {
        //with URN
        $this->_consignment->save();
        self::assertEquals('SAVED', $this->_consignment->getUrn());

        //amending consignment
        $this->_consignment->save();
        self::assertEquals('UPDATED', $this->_consignment->getUrn());
    }

    /**
     * @testCase Check delete function
     * @throws \Tuffnells\Exceptions\EndpointError
     */
    public function testDelete(): void{
        self::assertTrue($this->_consignment->delete());
    }

    /**
     * @testCase Set urn when building consignment
     */
    public function testConstructor(): void{
        $urn = 'TEST';
        $consignment = new Consignment($this->prophesize(Application::class)->reveal(), $urn);
        self::assertEquals($urn, $consignment->getUrn());
    }
}
