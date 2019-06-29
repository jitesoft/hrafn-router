<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  DispatcherInterface.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Contracts;

use Jitesoft\Exceptions\Http\Client\HttpMethodNotAllowedException;
use Jitesoft\Exceptions\Http\Client\HttpNotFoundException;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * DispatcherInterface
 *
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
interface DispatcherInterface {

    /**
     * @param string $method Method to dispatch.
     * @param string $target Target path.
     * @return RequestHandlerInterface
     * @throws HttpNotFoundException         On path not found.
     * @throws HttpMethodNotAllowedException On method not found.
     */
    public function dispatch(string $method,
                             string $target): RequestHandlerInterface;

}
