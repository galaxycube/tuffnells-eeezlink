<?php

namespace Tuffnells\Models\Consignment\Signatures;

use PHPUnit\Framework\TestCase;

class SignatureTest extends TestCase
{
    private Signature $_signature;

    /**
     * Set up default signature
     */
    public function setUp(): void {
        $this->_signature = new Signature();
    }

    /**
     * @testCase Chase signature set
     */
    public function testSetSignature(): void {
        $signature = 'Bert';
        $this->_signature->setSignature($signature);
        self::assertEquals($signature, $this->_signature->getSignature());
    }

    /**
     * @testCase Check date test
     */
    public function testSetDatetime() : void {
        $date = new \DateTime();
        $this->_signature->setDatetime($date);
        self::assertEquals($date, $this->_signature->getDatetime());
    }
}
