# Router

![Packagist Version](https://img.shields.io/packagist/v/hrafn/router?style=flat-square)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/hrafn/router?style=flat-square)
[![coverage report](https://gitlab.com/jitesoft/open-source/php/hrafn/router/badges/master/coverage.svg)](https://gitlab.com/jitesoft/open-source/php/hrafn/router/-/commits/master)
[![pipeline status](https://gitlab.com/jitesoft/open-source/php/hrafn/router/badges/master/pipeline.svg)](https://gitlab.com/jitesoft/open-source/php/hrafn/router/-/commits/master)

*Observe: This project is still a work in progress. Before version 1.0.0 it should be seen as highly unstable.*

## What is this?

Hrafn router is a router implementation using a tree structure to map up routes.  
The main reason of building it is/was to test if it was possible to build a router which could
process and route paths faster using a tree than not.

The project is in early stages and the current version is unstable. That is: *it's not recommended to use this router in production yet*.

## PSR and Interface binding

This project makes use and implements the following PSR standards:
 
 * PSR4 Auto-loading
 * PSR3 Logging
 * PSR7 Messages
 * PSR11 Container
 * PSR15 Handlers

Most interfaces are swap-able by using a container implementation of choice. If no container is defined a container using a `SimpleMap` structure will be used.

Feel free to read the API documentation for more information about what Interfaces you can use to customize the project to your own liking.

## Usage

The router expects each path to map to a given action. The action can be a standard callable or a method
in a class. You can group/namespace paths and placeholders for variables are available.

### Simple use-case

Example using a standard get route:

```php
<?php
use Hrafn\Router;

$router = new Router(/*pass your PSR-11 container here if wanted.*/);
$builder = $router->getBuilder();

$builder->get('test', function(ServerRequest $request) {
    // Do something.
    return new Response(); // PSR-7 response.
});

// Handle the request:
$router->handle(ServerRequest::fromGlobals()); // PSR-7 Request.
```

### Parameters

To bind parameters to a request path, the following standard is used:

```
{parameter} // Required parameter.
{?parameter} // Optional parameter.
```

Example:

```php
<?php
$builder->get('test/{name}', function($name) {
    
});
```

When `/test/abc` is called, the `$name` parameter will be set to `abc`.

### Using class handlers

Instead of using a function as the callback, a method from a class can be used.
When this is done, the router will try to create the class firstly by using the container, then depending
on the parameters in the class constructor, it will try to inject it with bound or default parameters.

When the class is created, the method will be called with the parameters that the route defines. If the first parameter is 
a implementation of the `RequestInterface` the request will be passed first.

### Middlewares

It's possible to use middlewares right out of the box, the middlewares must either implement the `Psr\Http\Server\MiddlewareInterface`
or be a callback function. They will be applied in the order they are passed. If using namespaces/groups, their middlewares will be applied first.
So it's easy to add a group which is restricted by using a middleware.
