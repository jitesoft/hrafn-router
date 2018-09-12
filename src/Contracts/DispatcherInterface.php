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
     * @param string $method
     * @param string $target
     * @return RequestHandlerInterface
     * @throws HttpNotFoundException
     * @throws HttpMethodNotAllowedException
     */
    public function dispatch(string $method, string $target): RequestHandlerInterface;

}
