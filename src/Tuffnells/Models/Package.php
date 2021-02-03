<?php


namespace Tuffnells\Models;


use Tuffnells\Exceptions\InvalidPackageQuantity;
use Tuffnells\Exceptions\InvalidPackageType;

class Package
{
    const PACKAGE_CARTON = 1;
    const PACKAGE_ROLL = 2;
    const PACKAGE_DRUM = 3;
    const PACKAGE_PALLET = 4;
    const PACKAGE_DATA_BAG = 5;

    private int $_type = self::PACKAGE_CARTON;
    private int $_weight = 0;
    private int $_quantity = 0;

    /**
     * Sets the package type
     *
     * @param int $type
     * @return $this
     * @throws InvalidPackageType
     */
    public function setType(int $type) : Package {
        if($type < 1 || $type > 5) {
            throw new InvalidPackageType();
        }

        $this->_type = $type;
        return $this;
    }

    /**
     * Returns the package type
     *
     * @return int
     */
    public function getType(): int {
        return $this->_type;
    }

    /**
     * Sets the weight
     *
     * @param int $weight
     * @return $this
     */
    public function setWeight(int $weight) : Package {
        $this->_weight = $weight;
        return $this;
    }

    /**
     * Returns the package weight
     *
     * @return int
     */
    public function getWeight() : int {
        return $this->_weight;
    }

    /**
     * Sets the number of packages
     *
     * @param int $quantity
     * @return $this
     */
    public function setQuantity(int $quantity) : Package {
        if($quantity < 1) {
            throw new InvalidPackageQuantity();
        }

        $this->_quantity = $quantity;
        return $this;
    }

    /**
     * Returns the number of packages
     *
     * @return int
     */
    public function getQuantity() : int{
        return $this->_quantity;
    }

    /**
     * Checks if the package is valid
     */
    public function isValid() :bool {
        if($this->getQuantity() < 1 || $this->getWeight() < 1)
            return false;
        return true;
    }
}