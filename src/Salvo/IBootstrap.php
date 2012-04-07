<?php
/**
 * This is part of the Salvo framework.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo;

/**
 * Bootstrap object interface
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
interface IBootstrap
{
    /**
     * This is method that the Salvo\Application executes when a bootstrap class is applied to it
     *
     * @abstract
     *
     * @param \Silex\Application $application
     *
     * @return void
     */
    public function configure(\Silex\Application $application);
}
