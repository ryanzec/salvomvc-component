<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo;

/**
 * PDO connection exception
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
interface IArrayable
{
	/**
	 * Converts an object into an array
	 *
	 * @abstract
	 *
	 * @return mixed[] The object in array form
	 */
	function toArray();
}
