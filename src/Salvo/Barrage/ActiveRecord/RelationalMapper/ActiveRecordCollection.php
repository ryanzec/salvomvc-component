<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo\Barrage\ActiveRecord\RelationalMapper;

/**
 * Active record collection object
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class ActiveRecordCollection implements \Countable, \SeekableIterator
{
    /**
     * Current position of the pointer
     *
     * @var int
     */
    private $position;

    /**
     * The collections data array
     *
     * @var array
     */
    private $data;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->validateData($data);
        $this->position = 0;
        $this->data = $data;
    }

    /**
     * Return how many object the collection has
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Return a specific object of the collection
     *
     * @param $position
     *
     * @return ActiveRecord
     */
    public function seek($position)
    {
        return (isset($this->data[$position])) ? $this->data[$position] : null;
    }

    /**
     * Return the current object of the collection
     *
     * @return mixed
     */
    public function current()
    {
        return $this->data[$this->position];
    }

    /**
     * Increase the position
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Returns the currenct position
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Check to see if the current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->data[$this->position]);
    }

    /**
     * Resets the position to the first element
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * returns the array of objects as an array of assoc arrays
     *
     * @return array
     */
    public function toArray()
    {
        $this->validateData($this->data);
        $arrayData = array();

        foreach($this->data as $object)
        {
            $arrayData[] = $object->toArray();
        }

        return $arrayData;
    }

    /**
     * Validates that all data passed (either object or array) is of type ActiveRecord
     *
     * @param array|object $data
     *
     * @throws ActiveRecordException
     */
    private function validateData($data)
    {
        if(is_array($data))
        {
            foreach($data as $object)
            {
                if(!$object instanceof ActiveRecord)
                {
                    throw new ActiveRecordException("Data stored in ActiveRecordCollection but be instance of ActiveRecord");
                }
            }
        }
        else
        {
            if(!$data instanceof ActiveRecord)
            {
                throw new ActiveRecordException("Data stored in ActiveRecordCollection but be instance of ActiveRecord");
            }
        }
    }
}
