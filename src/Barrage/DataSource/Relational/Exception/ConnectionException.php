<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Barrage\DataSource\Relational\Exception;

use Barrage\DataSource\Relational\IConnectionData;

/**
 * PDO connection exception
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class ConnectionException extends \Exception
{
    /**
     * @var null
     */
    private $connectionData;

    /**
     * Constructor
     *
     * @param $message
     * @param \Barrage\DataSource\Relational\IConnectionData|null $connectionData\
     */
    public function __construct($message,IConnectionData $connectionData = null)
    {
        parent::__construct($message);
        $this->connectionData = $connectionData;
    }
}
