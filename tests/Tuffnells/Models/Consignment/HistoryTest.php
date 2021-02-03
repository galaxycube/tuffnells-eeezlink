<?php

namespace Tuffnells\Models\Consignment;

use PHPUnit\Framework\TestCase;
use Tuffnells\Models\Consignment;
use Tuffnells\Models\Consignment\History\Log;

class HistoryTest extends TestCase
{
    /**
     * @var History
     */
    private History $_logs;

    /**
     * Set up default history object
     */
    public function setUp(): void
    {
        $this->_logs = new History();
    }

    /**
     * @testCase Check remove functionality
     */
    public function testRemove(): void {
        $log = new Log();
        $log->setDate(new \DateTime());
        $log->setDescription('Blah');
        $log->setDeliveryDepot('100');
        $log->setRoundNumber('100');
        $log->setPackagesReceived(1);
        $log->setPackagesDelivered(0);
        $log->setDeliveryDate(new \DateTime());
        $this->_logs->add($log);
        self::assertEquals(1,$this->_logs->count());
        self::assertTrue($this->_logs->remove($log));
        self::assertFalse($this->_logs->remove($log));

    }

    /**
     * @testCase Check count functionality
     */
    public function testCount(): void {
        self::assertEquals(0,$this->_logs->count());
        $log = new Log();
        $log->setDate(new \DateTime());
        $log->setDescription('Blah');
        $log->setDeliveryDepot('100');
        $log->setRoundNumber('100');
        $log->setPackagesReceived(1);
        $log->setPackagesDelivered(0);
        $log->setDeliveryDate(new \DateTime());
        $this->_logs->add($log);
        self::assertEquals(1,$this->_logs->count());
        $this->_logs->remove($log);
        self::assertEquals(0,$this->_logs->count());
    }

    /**
     * @testCase Standard array access tests
     */
    public function testStandardFunctions(): void {
        $total = random_int(1,30);
        for($i=0; $i < $total; $i++)
        {
            $log = new Log();
            $log->setDate(new \DateTime());
            $log->setDescription('Blah');
            $log->setDeliveryDepot('100');
            $log->setRoundNumber('100');
            $log->setPackagesReceived($i+1);
            $log->setPackagesDelivered($i+1);
            $log->setDeliveryDate(new \DateTime());
            $this->_logs->add($log);
        }

        $item = random_int(1,$total-1);
        self::assertInstanceOf( Log::class, $this->_logs->offsetGet($item));
        self::assertNull($this->_logs->offsetGet($total));

        $log = new Log();
        $log->setDate(new \DateTime());
        $log->setDescription('Blah');
        $log->setDeliveryDepot('100');
        $log->setRoundNumber('100');
        $log->setPackagesReceived(1);
        $log->setPackagesDelivered(0);
        $log->setDeliveryDate(new \DateTime());
        $this->_logs->add($log);

        self::assertNotEquals( $log, $this->_logs->offsetGet($item));
        $this->_logs->offsetSet($item, $log);
        self::assertEquals( $log, $this->_logs->offsetGet($item));
        $this->_logs->offsetSet(null, $log);
        self::assertEquals( $log, $this->_logs->offsetGet($total));
        self::assertTrue($this->_logs->offsetExists($total));
        $this->_logs->offsetUnset($total);
        self::assertNull($this->_logs->offsetGet($total));
        self::assertFalse($this->_logs->offsetExists($total));
    }


    public function testGetStatus(): void {
        $log = new Log();
        $log->setDate(new \DateTime());
        $log->setDescription('Delivered');
        $log->setDeliveryDepot('100');
        $log->setRoundNumber('100');
        $log->setPackagesReceived(1);
        $log->setPackagesDelivered(0);
        $log->setDeliveryDate(new \DateTime());
        $this->_logs->add($log);
        self::assertEquals(Consignment::STATUS_DELIVERED, $this->_logs->getStatus());
        $log->setDescription('Out to deliver');
        self::assertEquals(Consignment::STATUS_OUT_FOR_DELIVERY, $this->_logs->getStatus());
        $this->_logs->remove($log);
        self::assertEquals(Consignment::STATUS_IN_TRANSIT, $this->_logs->getStatus());
    }
}
