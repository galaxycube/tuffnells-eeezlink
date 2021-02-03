<?php


namespace Tuffnells\Models\Consignment;


use Tuffnells\Models\Consignment;
use Tuffnells\Models\Consignment\History\Log;

class History implements \ArrayAccess, \Countable
{
    private array $_container = [];

    /**
     * Adds log to history
     *
     * @param Log $log
     */
    public function add(Log $log) {
        $this->_container[] = $log;
    }

    /**
     * Removes log from history
     *
     * @param Log $log
     * @return bool
     */
    public function remove(Log $log) {
        $count = count($this->_container);
        for($i =0; $i < $count; $i++){
            if($log === $this->_container[$i]) {
                unset($this->_container[$i]);
                return true;
            }
        }
        return false;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_container[] = $value;
        } else {
            $this->_container[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->_container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->_container[$offset]);
    }

    public function offsetGet($offset) {
        return $this->_container[$offset] ?? null;
    }

    /**
     * @return int
     */
    public function getStatus(): int {
        $count = count($this->_container);
        if($count > 0)
        {
            switch($this->_container[$count-1]->getDescription())
            {
                case 'Delivered':
                    return Consignment::STATUS_DELIVERED;
                case 'Out to deliver':
                    return Consignment::STATUS_OUT_FOR_DELIVERY;
                case 'Created By EZEEWEB':
                    return Consignment::STATUS_AWAITING_PICKUP;
            }
        }
        return Consignment::STATUS_IN_TRANSIT;
    }

    /**
     * returns the number of logs in the history collection
     * @return int
     */
    public function count()
    {
        return count($this->_container);
    }
}