<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  HandleMiddlewareTrait.php - Part of the router project.

  © - Jitesoft 2019
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\RequestHandler;

use Hrafn\Router\Action;
use Hrafn\Router\Router;
use Jitesoft\Exceptions\Http\Server\HttpInternalServerErrorException;
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
     * @throws HttpInternalServerErrorException If middleware was a string which could not be resolved.
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

        $middleware = $this->action->getMiddlewares()->dequeue();
        if (is_string($middleware)) {

            if($this->container->has($middleware)) {
                $middleware = $this->container->get($middleware);
            } else {
                throw new HttpInternalServerErrorException('Middleware {name} was not found in the container nor was it an instance.');
            }

        }

        return $middleware
            ->process(
                $request,
                $this
            );
    }

}
