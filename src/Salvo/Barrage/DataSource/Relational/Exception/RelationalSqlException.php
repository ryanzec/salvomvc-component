<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo\Barrage\DataSource\Relational\Exception;
/**
 * Relation sql exception
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class RelationalSqlException extends \Exception
{
	/**
	 * Last sql call attempted before exception
	 *
	 * @var string
	 */
	private $sql;

	/**
	 * Constructor
	 *
	 * @param $message
	 * @param string $sql
	 */
	public function __construct($message, $sql = null)
	{
		parent::__construct($message);
		$this->sql = $sql;
	}
}
