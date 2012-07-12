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
 * Exception thrown when a parameter is not valid
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * @api
 */
class InvalidParameterException extends \InvalidArgumentException implements ExceptionInterface
{
}
