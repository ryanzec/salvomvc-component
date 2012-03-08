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
 * Base web test case for Salvo
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class WebTestCase extends \Silex\WebTestCase
{
    /**
     * Creates and return an instance of the Silex\Application object
     *
     * @param void
     *
     * @return \Silex\Application Instance of the Silex\Application object
     */
    public function createApplication()
    {
        $application = new \Silex\Application();
        $salvoApplication = \Salvo\Salvo::getInstance($application, __DIR__ . '/..');
        return $salvoApplication->getSilexApplication();
    }
}
