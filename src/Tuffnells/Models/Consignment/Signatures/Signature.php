<?php
namespace Tuffnells\Models\Consignment\Signatures;

class Signature {

    private string $_signature;
    private \DateTime $_datetime;

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->_signature;
    }

    /**
     * @param string $signature
     * @return Signature
     */
    public function setSignature(string $signature): Signature
    {
        $this->_signature = $signature;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDatetime(): \DateTime
    {
        return $this->_datetime;
    }

    /**
     * @param \DateTime $datetime
     * @return Signature
     */
    public function setDatetime(\DateTime $datetime): Signature
    {
        $this->_datetime = $datetime;
        return $this;
    }
}