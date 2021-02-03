<?php

namespace Tuffnells\Models;

use PHPUnit\Framework\TestCase;

class CityRegionTest extends TestCase
{
    private CityRegion $_cityRegion;

    /**
     * Sets up for each test
     */
    public function setUp(): void
    {
        $this->_cityRegion = new CityRegion('BLANK', 'BLANK');
    }

    /**
     * @testCase Test City Set
     */
    public function testSetCity() {
        self::assertEquals('BLANK', $this->_cityRegion->getCity());
        $city = 'AIRDRIE';
        $this->_cityRegion->setCity($city);
        self::assertEquals($city, $this->_cityRegion->getCity());
    }

    /**
     * @testCase Test Region Set
     */
    public function testSetRegion() {
        self::assertEquals('BLANK', $this->_cityRegion->getRegion());
        $region = 'LANARKSHIRE';
        $this->_cityRegion->setRegion($region);
        self::assertEquals($region, $this->_cityRegion->getRegion());
    }
}
