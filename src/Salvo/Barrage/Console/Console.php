<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo\Barrage\Console;

use Symfony\Component\Console\Application;
use Salvo\Barrage\Console\Command\ActiveRecord\Relational;

/**
 * Barrage console
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class Console extends Application
{
	/**
	 * Calculator constructor.
	 */
	public function __construct() {
		parent::__construct('Barrage Console', '1.0');

		$this->addCommands(array(
			new Command\ActiveRecord\Relational\ModelBuilder()
		));
	}
}
