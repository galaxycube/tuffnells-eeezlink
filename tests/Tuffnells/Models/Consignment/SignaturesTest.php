<?php

namespace Tuffnells\Models\Consignment;

use PHPUnit\Framework\TestCase;
use Tuffnells\Models\Consignment\Signatures\Signature;

class SignaturesTest extends TestCase
{
    private Signatures $_signatures;

    public function setUp(): void
    {
        $this->_signatures = new Signatures();
    }

    /**
     * @testCase Check count function
     */
    public function testCount()
    {
        self::assertEquals(0, $this->_signatures->count());
        $total = rand(1,30);
        for($i=0; $i < $total; $i++)
        {
            $signature = new Signature();
            $signature->setSignature('Test ' . $i);
            $signature->setDatetime(new \DateTime());
            $this->_signatures->add($signature);
        }

        self::assertEquals($total, $this->_signatures->count());
    }

    /**
     * @testCase Check count function
     */
    public function testAdd()
    {
        $total = rand(1,30);
        for($i=0; $i < $total; $i++)
        {
            $signature = new Signature();
            $signature->setSignature('Test ' . $i);
            $signature->setDatetime(new \DateTime());
            $this->_signatures->add($signature);
            $lastSignature = $signature;
        }

        $this->_signatures->remove($lastSignature);
        self::assertEquals($total-1, $this->_signatures->count());
        self::assertFalse($this->_signatures->remove($lastSignature));
        self::assertEquals($total-1, $this->_signatures->count());
    }

    /**
     * @testCase Standard array access tests
     */
    public function testStandardFunctions(): void {
        $total = random_int(1,30);
        for($i=0; $i < $total; $i++)
        {
            $signature = new Signature();
            $signature->setSignature('Test ' . $i);
            $signature->setDatetime(new \DateTime());
            $this->_signatures->add($signature);
        }

        $item = random_int(1,$total-1);
        self::assertInstanceOf( Signature::class, $this->_signatures->offsetGet($item));
        self::assertNull($this->_signatures->offsetGet($total));

        $signature = new Signature();
        $signature->setSignature('Testing');
        $signature->setDatetime(new \DateTime());
        $this->_signatures->add($signature);

        self::assertNotEquals( $signature, $this->_signatures->offsetGet($item));
        $this->_signatures->offsetSet($item, $signature);
        self::assertEquals( $signature, $this->_signatures->offsetGet($item));
        $this->_signatures->offsetSet(null, $signature);
        self::assertEquals( $signature, $this->_signatures->offsetGet($total));
        self::assertTrue($this->_signatures->offsetExists($total));
        $this->_signatures->offsetUnset($total);
        self::assertNull($this->_signatures->offsetGet($total));
        self::assertFalse($this->_signatures->offsetExists($total));
    }



}
