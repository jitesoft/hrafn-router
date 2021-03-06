<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ReflectionCallbackHandlerackHandler.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\RequestHandler;

use Closure;
use Hrafn\Router\Action;
use Hrafn\Router\Contracts\ParameterExtractorInterface;
use Jitesoft\Exceptions\Http\Client\HttpBadRequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use ReflectionFunction;

/**
 * ReflectionCallbackHandler
 *
 * @author  Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ReflectionCallbackHandler implements RequestHandlerInterface {
    use HandleMiddlewareTrait;

    private Closure $callback;
    private ParameterExtractorInterface $parameterExtractor;
    private Action $action;

    /**
     * ReflectionCallbackHandler constructor.
     *
     * @param callable                    $callback           Callback to handle.
     * @param ParameterExtractorInterface $parameterExtractor Extractor for the path parameters.
     * @param Action                      $action             Action object.
     */
    public function __construct(
        callable $callback,
        ParameterExtractorInterface $parameterExtractor,
        Action $action
    ) {
        $this->callback           = $callback;
        $this->parameterExtractor = $parameterExtractor;
        $this->action             = $action;
    }

    /**
     * Handle the request and return a response.
     *
     * @param ServerRequestInterface $request Request to handle.
     * @return ResponseInterface
     * @throws ReflectionException     On reflection error.
     * @throws HttpBadRequestException On bad request.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        if ($this->action->getMiddlewares()->count() !== 0) {
            return $this->process($request);
        }

        $reflect    = new ReflectionFunction($this->callback);
        $parameters = $reflect->getParameters();

        $arguments = [];
        if (count($parameters) === 0) {
            $arguments[] = $request;
        }

        $parsedParams = $this->parameterExtractor->getUriParameters(
            $this->action->getPattern(),
            trim($request->getRequestTarget(), '/')
        );

        // The controller does not HAVE to have the required parameters, they are just required
        // in the uri, not the controller.
        // So check if it exists in the list of parameters, and if so, add it.
        foreach ($parameters as $parameter) {
            // If a parameter exists in the controller, it have to be set as long as it is not optional, in that case
            // we can set it to null.
            $name = mb_strtolower($parameter->getName());
            if ($parsedParams->has($name)) {
                $arguments[] = $parsedParams[$name];
            } else {
                if ($parameter->isOptional()) {
                    $arguments[] = null;
                } else {
                    // In some cases, user want to pass the request to the handler, we can't expect them to use a certain
                    // name, so instead, we force them to use a RequestInterface implementation as the parameter type.
                    $class = null;
                    try {
                        $class = $parameter->getClass();
                        if ($class
                            && $class->implementsInterface(RequestInterface::class)
                        ) {
                            $arguments[] = $request;
                            continue;
                        }
                    } catch (ReflectionException $ex) {
                        // Do nothing.
                        continue;
                    }
                    // Finally, if it's not a request interface, it should be thrown as a bad request, the argument does
                    // not exist.
                    throw new HttpBadRequestException(
                        sprintf(
                            'Parameter in handler does not exist in pattern (%s).',
                            $parameter->getName()
                        )
                    );
                }
            }
        }

        return $reflect->invokeArgs($arguments);
    }

}
