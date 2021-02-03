<?php

namespace Tuffnells\Models\Consignment\History;

/**
 * Class Log
 * Represents a history log in a consignment
 *
 * @package Tuffnells\Models\Consignment\History
 */
class Log
{
    private \DateTime $_date;
    private string $_description;
    private string $_deliveryDepot;
    private string $_roundNumber;
    private \DateTime $_deliveryDate;
    private int $_packagesReceived;
    private int $_packagesDelivered;

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->_date;
    }

    /**
     * @param \DateTime $date
     * @return Log
     */
    public function setDate(\DateTime $date): Log
    {
        $this->_date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->_description;
    }

    /**
     * @param string $description
     * @return Log
     */
    public function setDescription(string $description): Log
    {
        $this->_description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryDepot(): string
    {
        return $this->_deliveryDepot;
    }

    /**
     * @param string $deliveryDepot
     * @return Log
     */
    public function setDeliveryDepot(string $deliveryDepot): Log
    {
        $this->_deliveryDepot = $deliveryDepot;
        return $this;
    }

    /**
     * @return string
     */
    public function getRoundNumber(): string
    {
        return $this->_roundNumber;
    }

    /**
     * @param string $roundNumber
     * @return Log
     */
    public function setRoundNumber(string $roundNumber): Log
    {
        $this->_roundNumber = $roundNumber;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeliveryDate(): \DateTime
    {
        return $this->_deliveryDate;
    }

    /**
     * @param \DateTime $deliveryDate
     * @return Log
     */
    public function setDeliveryDate(\DateTime $deliveryDate): Log
    {
        $this->_deliveryDate = $deliveryDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getPackagesReceived(): int
    {
        return $this->_packagesReceived;
    }

    /**
     * @param int $packagesReceived
     * @return Log
     */
    public function setPackagesReceived(int $packagesReceived): Log
    {
        $this->_packagesReceived = $packagesReceived;
        return $this;
    }

    /**
     * @return int
     */
    public function getPackagesDelivered(): int
    {
        return $this->_packagesDelivered;
    }

    /**
     * @param int $packagesDelivered
     * @return Log
     */
    public function setPackagesDelivered(int $packagesDelivered): Log
    {
        $this->_packagesDelivered = $packagesDelivered;
        return $this;
    }

}