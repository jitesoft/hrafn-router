<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  DispatcherInterface.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Contracts;

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
     * @return mixed
     */
    public function dispatch(string $method, string $target): RequestHandlerInterface;

}
