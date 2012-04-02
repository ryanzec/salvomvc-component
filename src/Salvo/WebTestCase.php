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
        return \Salvo\Salvo::getSilexApplication();
    }

    /**
     * Used to access ->app as ->application as application is the standard name in Salvo
     *
     * @param $member
     *
     * @return mixed
     */
    public function __get($member)
    {
        if($member === 'application')
        {
            return $this->app;
        }

        return null;
    }

}
