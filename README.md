Salvo, A Lightweight PHP MVC Framework
======================================

Salvo is a lightweight PHP MVC framework that is a combination of Silex, third party libraries, and a custom built data abstraction layer/active record library called Barrage (which may or may not be released as it's own independent library once Salvo has reach Version 1).  This framework is designed for people who want something more powerful than just Silex itself but something with less complexity and bloat of a framework like Symfony2.

## History
This framework steamed from myself liking Symfony2, however, I felt that Symfony2 was more complex than it needed to be, has a bunch of functionality I rally didn't care about, and had certain libraries I did not want to use.  Silex is a frameowrk built on a select number of Symfony2 Component and it just happened to contain most of the components I cared about.  I took Silex, a couple of other third party libraries, and also built a custom data abstraction layer/active record system and pulled them all together to what I call Salvo.

## Requirements

PHP 5.3.2 or later

## Use For Production

Until this project reaches version 1.0, I would highly suggestion that you don't use this framework in a production environment.  The following describes the different stages Salvo/Barrage will/can go through:

* 0.1.x -> 0.4.x: In a pre-alpha state which means that pretty anything is up in the air for changing
* 0.5.x -> 0.7.x: In an alpha state which still means that anything is still up in the air for changing
* 0.8.x -> 0.9.x: API should be in a stable state and very few changes, if any, should happen (though if required, the API might still change)

Again, this goes for both Salvo and Barrage as they are being developed at the same time.

One thing to especially watch out for in the pre-alpha states is the use of exceptions.  A lot of places might be using standard Exception however chances are most of those will change to custom exception objects (but even custom thrown exceptions are not safe in the pre-alpha stage).

## Setup Instructions

Place Salvo anywhere on your web server, point a vhost to the web directory, and your set to go.

## Directory Structure

There are a number of different directories that Salvo comes with.  Those directories are as follows:

* configuration: This is where most configuration files should go (configuration files with passwords such as ones for Barrage can be place completely outside the web server directory)
* logs: Designed for log files
* src: This is where the code for your project goes
* tests: This is where all tests for your project goes
* vendor: This is where all libraries are stored
* web: This is where all the web accessible files should be stored (front controller, css, javascript, images etc...)

## Bootstrap Process

The setup process for the most part is an automatic process however if you need to apply and special code before Silex\Application->run() is executed, you do have the options of adding in one or more bootstrap objects.  These bootstrap objects will allow you to be able to execute any code you want against the Silex\Application object before Silex\Application->run() is executed.  All you have to do is add in the appropriate bootstrap configurations in the application.yml file.

## Configuration Notes

Most of the configurations are pretty self explanatory however there is one things that should be mentioned.

### Service Providers

Any configuration that is either names path or end in _path has special functionality applied to it for service providers.  If the value of the does not start with a forward slash (/), that will assume that you are giving a path relative to the root directory of Salvo.  If the value starts with a forward slash (/), that will assume that you are giving an absolute path.  The only reason you should try to give an absolute path is if a configuration file path has to be configured and it contains secure information (such as the data source password that Barrage requires in it's configuration).

### Routes

#### Route Paths

You don't need to route routes if you are fine with the default format that is provided.  You still need to configure the controller itself but if you don't configure specific routes that format default to /[controller base_route configuration]/[action method lowercase with underscores with the ending action].  So for example, if the DashboardController's base_route is desktop and you have a action called twigCustomBlockAction, the route path to that action will be /desktop/twig_custom_block.

#### Route Names

If you don't config a name for a route, it defaults to [controller's base_route]_[action method lowercase with underscores with the ending action].  So for example, if the DashboardController's base_route is desktop and you have a action called twigCustomBlockAction, the route name for that action will be desktop_twig_custom_block.

## Model Generation

There is very basic functionality for generating model files through barrage's console application however it is not at a state where I want to document it.  It only works with databases following a certain naming convention.

## Coding Standards

TBD

## Third Party Library Credits

Salvo uses a number of third party libraries and I just want to give them some credit here:

Silex - Used for the core framework
Yaml - The Symfony2 component that is used for reading configuration files
Twig - PHP template engine
Monolog - Used for logging
Barrage - A database abstraction layer/active record library (this is something that I am building and will maintain)

Without these libraries, Salvo would not be possible.

## License

Salvo itself is released under the MIT license (see attached LICENSE file for details).

All third party libraries are released under their own license which is included in each respective vendor's folder.
