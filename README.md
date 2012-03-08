# Salvo, A Lightweight PHP MVC Framework #

Salvo is a lightweight PHP MVC framework that is a combination of Silex/Symfony2 Components, other third party libraries, and some additional custom functionality added on top of it.  This framework is designed for people who want something more powerful than something like Silex itself but something with less complexity and bloated like Symfony.

## History ##

I tried out Symfony2 and really liked the core of the framework but there were 2 issues I had.  One is that I fealt that it was very bloated.  It had a lot of functionality that I really did not use on a common bases and that I did not want added to to core of my framework.  The other thing was that it seemed overly comple to me (partly because of all the functionality it had that I really did not want).  This is why I took Silex, other Symfony2 Components, and other third part libraries and used those are the based for my framework (also adding in functionality of my own).

## Requirements ##

PHP 5.3.2 or later

## Use For Production ##

Until this project reaches version 1.0, I would highly suggestion that you don't use this framework in a production environment.  The following describes the different stages Salvo will/can go through:

* 0.1.x -> 0.4.x: In a pre-alpha state which means that pretty anything is up in the air for changing
* 0.5.x -> 0.7.x: In an alpha state which still means that anything is still up in the air for changing
* 0.8.x -> 0.9.x: In Beta state which means API should be in a stable state and very few changes, if any, should happen (though if required, the API might still change)
* 1.0-rc*: API will be stable from this point on and focus is only on fixing bugs for official 1.0 release

One thing to especially watch out for in the pre-alpha states is the use of exceptions.  A lot of places might be using standard Exception however chances are most of those will change to custom exception objects (but even custom thrown exceptions are not safe in the pre-alpha stage).

## Setup Instructions ##

This just included the core code for Salvo to run however this project alone is not enough to creating a working copy of Salvo.  Once it get into the alpha state, I will be creating ull working copies of Salvo for download for testing purposes.

## <font style="color: red;">Warning About The Following Information</font> ##

The rest of this file contains some very basic documentation.  While in the pre-alpha state, this documentation is not gauranteed to be accurate as I except to be the only person actively using this code.  One Salvo gets into the alpha state, I will do my best to keep this basic documentation accurate.

## Directory Structure ##

There are a number of different directories that Salvo comes with.  Those directories are as follows:

* configuration: This is where most configuration files should go (configuration files with passwords such as ones for Barrage can be place completely outside the web server directory for security purposes)
* logs: Designed for log files
* src: This is where the code for your project goes
* tests: This is where all tests for your project goes
* vendor: This is where all libraries are stored
* web: This is where all the web accessible files should be stored (front controller, css, javascript, images etc...)

## Bootstrap Process ##

The setup process for the most part is an automatic process however if you need to apply and special code before Silex\Application->run() is executed, you do have the options of adding in one or more bootstrap objects.  These bootstrap objects will allow you to be able to execute any code you want against the Silex\Application object before Silex\Application->run() is executed.  All you have to do is add in the appropriate bootstrap configurations in the application.yml file.

## Configuration Notes ##

Most of the configurations are pretty self explanatory however there is one things that should be explained a little in detail.

### Service Providers ###

Any configuration that is either names path or end in _path has special functionality applied to it for service providers.  If the value of the does not start with a forward slash (/), that will assume that you are giving a path relative to the root directory of Salvo.  If the value starts with a forward slash (/), that will assume that you are giving an absolute path.  The only reason you should try to give an absolute path is if a configuration file path has to be configured and it contains secure information (such as the data source password that Barrage requires in it's configuration).

### Routes ###

#### Route Paths ####

You don't need to route routes if you are fine with the default format that is provided.  You still need to configure the controller itself but if you don't configure specific routes that format default to /[controller base_route configuration]/[action method lowercase with underscores with the ending action].  So for example, if the DashboardController's base_route is desktop and you have a action called twigCustomBlockAction, the route path to that action will be /desktop/twig_custom_block.

#### Route Names ####

If you don't config a name for a route, it defaults to [controller's base_route]_[action method lowercase with underscores with the ending action].  So for example, if the DashboardController's base_route is desktop and you have a action called twigCustomBlockAction, the route name for that action will be desktop_twig_custom_block.

## Model Generation ##

There is very basic functionality for generating model files through barrage's console application however it is not at a state where I want to document it.  It only works with databases following a certain naming convention.

## Events ##

Now let talk about both events that salvo listens to and event the salvo itself has

### Event Salvo Listens To ###

These are the follow events that salvo performs actions on, the priority, and what salvo does:

SilexEvents::BEFORE (silex.before) : 1000 : Added the items requested_controller and requested_action to the $application object. If you want to run anything that required you knowing either of those pieces of information, you will need to make you priority less than 1000.

### Events Salvo Internally Has ###

TBD

## Coding Standards ##

TBD

## Third Party Library Credits ##

Salvo uses a number of third party libraries and I just want to give them some credit here:

* Composer - Dependency management
* Silex - Used for the core framework
* Symfony2 Yaml: Reading configuration files
* Twig: PHP template engine
* Monolog: Logging

Without these libraries, Salvo would not be possible.

## License ##

Salvo itself is released under the MIT license (see attached LICENSE file for details).

All third party libraries are released under their own license which is included in each respective vendor's folder.
