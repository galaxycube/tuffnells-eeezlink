<?php

namespace Tuffnells\Models;

use PHPUnit\Framework\TestCase;
use Tuffnells\Exceptions\InvalidPackageQuantity;
use Tuffnells\Exceptions\InvalidPackageType;

class PackageTest extends TestCase
{
    private Package $_package;

    public function setUp(): void
    {
        $this->_package = new Package();
    }

    /**
     * @testCase Set package type
     */
    public function testSetType(): void {
        $this->_package->setType(Package::PACKAGE_PALLET);
        self::assertEquals(Package::PACKAGE_PALLET, $this->_package->getType());
    }

    public function testSetTypeInvalid(): void {
        $this->expectException(InvalidPackageType::class);
        $this->_package->setType(100);
        $this->_package->setType(-100);
    }

    /**
     * @testCase Checks quantity correct
     */
    public function testSetValidQuantity(): void {
        $this->_package->setQuantity(100);
        self::assertEquals(100, $this->_package->getQuantity());
    }

    /**
     * @testCase Set an invalid package quantity (negative or 0)
     */
    public function testSetInvalidQuantity(): void {
        $this->expectException(InvalidPackageQuantity::class);
        $this->_package->setQuantity(-1);
    }

    /**
     * @testCase Check validation check works on invalid qweight
     */
    public function testInvalidPackageQuantity(): void {
        $this->_package->setQuantity(1);
        self::assertFalse($this->_package->isValid());
    }

    /**
     * @testCase Check validation check works on invalid quantity
     */
    public function testInvalidPackageWeight(): void {
        $this->_package->setWeight(1);
        self::assertFalse($this->_package->isValid());
    }

    /**
     * @testCase Valid package tyep
     */
    public function testValidPackage(): void {
        $this->_package->setQuantity(1);
        $this->_package->setWeight(10);
        self::assertTrue($this->_package->isValid());
    }
}
