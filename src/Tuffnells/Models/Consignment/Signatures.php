<?php


namespace Tuffnells\Models\Consignment;


use Tuffnells\Models\Consignment;

class Signatures implements \ArrayAccess, \Countable
{
    /**
     * @var array
     */
    private array $_container = [];

    /**
     * Adds log to history
     *
     * @param Consignment\Signatures\Signature $signature
     */
    public function add(Consignment\Signatures\Signature $signature) {
        $this->_container[] = $signature;
    }

    /**
     * Removes log from history
     *
     * @param Consignment\Signatures\Signature $log
     * @return bool
     */
    public function remove(Consignment\Signatures\Signature $log) {
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

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->_container[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        unset($this->_container[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset) {
        return $this->_container[$offset] ?? null;
    }

    /**
     * returns the number of logs in the signature collection
     * @return int
     */
    public function count(): int
    {
        return count($this->_container);
    }
}