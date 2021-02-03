<?php

namespace Tuffnells\Models\Consignment\History;

use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    private Log $_log;

    public function setUp(): void
    {
        $this->_log = new Log();
    }

    public function testSetDeliveryDepot(): void {
        $depot = '44';
        $this->_log->setDeliveryDepot($depot);
        self::assertEquals($depot, $this->_log->getDeliveryDepot());
    }

    public function testSetRoundNumber(): void {
        $roundNumber = '44';
        $this->_log->setRoundNumber($roundNumber);
        self::assertEquals($roundNumber, $this->_log->getRoundNumber());
    }

    public function testSetPackagesReceived(): void {
        $this->_log->setPackagesReceived(1);
        self::assertEquals(1, $this->_log->getPackagesReceived());
    }

    public function testSetPackagesDelivered(): void {
        $this->_log->setPackagesDelivered(1);
        self::assertEquals(1, $this->_log->getPackagesDelivered());
    }

    public function testGetDeliveryDate(): void {
        $date = new \DateTime();
        $this->_log->setDeliveryDate($date);
        self::assertEquals($date, $this->_log->getDeliveryDate());
    }

    public function testGetDate(): void {
        $date = new \DateTime();
        $this->_log->setDate($date);
        self::assertEquals($date, $this->_log->getDate());
    }
}
