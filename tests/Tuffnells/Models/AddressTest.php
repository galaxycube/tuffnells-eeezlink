<?php

namespace Tuffnells\Models;

use PHPUnit\Framework\TestCase;
use Tuffnells\Application;
use Tuffnells\Exceptions\PostcodeNotValid;

class AddressTest extends TestCase
{
    private Address $_address;

    public function setUp(): void {
        $mock = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getCityRegion')->willReturnCallback(
            function(string $postcode) : CityRegion {
                if($postcode === 'ML6 9HB')
                    return new CityRegion('AIRDRIE', 'LANARKSHIRE');

                throw new PostcodeNotValid();
            }
        );

        $this->_address = new Address($mock);
    }

    public function testTailliftPhone() :void
    {
        self::assertFalse($this->_address->isRequiredTailLift());
        $this->_address->setRequiredTailLift(true);
        self::assertTrue($this->_address->isRequiredTailLift());
    }

    public function testInstructions(): void {
        $test = 'Hello Instructions';
        self::assertEmpty($this->_address->getInstructions());
        $this->_address->setInstructions($test);
        self::assertEquals($test, $this->_address->getInstructions());
    }

    public function testValidPostcode(): void {
        $postcode = 'ML6 9HB';
        $this->_address->setPostcode($postcode);
        self::assertEquals($postcode, $this->_address->getPostcode());
        self::assertEquals('AIRDRIE', $this->_address->getCity());
        self::assertEquals('LANARKSHIRE', $this->_address->getRegion());
    }

    public function testInvalidPostcode():void {
        $this->expectException(PostcodeNotValid::class);
        $postcode = 'ML69HB';
        $this->_address->setPostcode($postcode);
    }

    public function testSetCompany(): void {
        $company = 'A Company Name';
        $this->_address->setCompany($company);
        self::assertEquals($company, $this->_address->getCompany());
    }

    public function testSetAddressLines(): void{
        for($i=1;$i<4;$i++) {
            $line = 'Line Address ' . $i;
            $method = 'setAddressLine' . $i;
            $this->_address->$method($line);
            $method = 'getAddressLine' . $i;
            self::assertEquals($line, $this->_address->$method());
        }
    }

    public function testSetCountry(): void{
        self::assertEquals(44, $this->_address->getCountryCode());
        $this->_address->setCountryCode(45);
        self::assertEquals(45, $this->_address->getCountryCode());
    }

    public function testSetContact(): void{
        $name = 'Test McTest';
        $this->_address->setContactName($name);
        self::assertEquals($name, $this->_address->getContactName());
    }

    public function testSetPhone(): void{
        $phone = '0123456789';
        $this->_address->setContactPhone($phone);
        self::assertEquals($phone, $this->_address->getContactPhone());
    }

    public function testSetEmail(): void{
        $email = 'example@example.org';
        $this->_address->setContactEmail($email);
        self::assertEquals($email, $this->_address->getContactEmail());
    }
}
