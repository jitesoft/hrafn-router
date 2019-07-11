<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  HandleMiddlewareTrait.php - Part of the router project.

  © - Jitesoft 2019
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\RequestHandler;

use Hrafn\Router\Action;
use Hrafn\Router\Router;
use Psr\Http\Message\RequestInterface;

/**
 * Trait HandleMiddlewareTrait
 *
 * @property Action $action
 * @method handle(RequestInterface $request)
 */
trait HandleMiddlewareTrait {

    /**
     * Process middlewares.
     *
     * @param RequestInterface $request Request being handled.
     * @return mixed
     */
    public function process(RequestInterface $request) {
        if (count(Router::$disabledMiddleware) > 0) {
            $disabled = in_array(
                get_class($this->action->getMiddlewares()->peek()),
                Router::$disabledMiddleware
            );

            if ($disabled) {
                $this->action->getMiddlewares()->dequeue();
                return $this->handle($request);
            }
        }

        return $this->action
            ->getMiddlewares()
            ->dequeue()
            ->process(
                $request,
                $this
            );
    }

}
