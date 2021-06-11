<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ActionInterface.php - Part of the router project.

  © - Jitesoft 2018-2021
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Contracts;

use Hrafn\Router\Method;
use Jitesoft\Utilities\DataStructures\Queues\QueueInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ActionInterface
 *
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
interface ActionInterface {

    /**
     * Get the request method, the method string will correspond to the Hrafn\Router\Method constants.
     *
     * @see Method
     * @return string
     */
    public function getMethod(): string;

    /**
     * Get all middlewares as a queue.
     *
     * @return QueueInterface
     */
    public function getMiddlewares(): QueueInterface;

    /**
     * Get the pattern used by the action, the pattern should not contain any group path nor query parameters and such.
     * E.G., /path/to/action/{with}/params
     *
     * @return string
     */
    public function getPattern(): string;

    /**
     * Get the request handler instance.
     *
     * @return RequestHandlerInterface
     */
    public function getHandler(): RequestHandlerInterface;

}
