<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ReflectionClassHandler.phpandler.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\RequestHandler;

use Hrafn\Router\Action;
use Hrafn\Router\Contracts\ParameterExtractorInterface;
use Jitesoft\Container\Container;
use Jitesoft\Container\Injector;
use Jitesoft\Exceptions\Http\Client\HttpBadRequestException;
use Jitesoft\Exceptions\Http\Client\HttpMethodNotAllowedException;
use Jitesoft\Exceptions\Http\Server\HttpInternalServerErrorException;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionException;

/**
 * ReflectionClassHandler
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ReflectionClassHandler implements RequestHandlerInterface {

    private $className;
    private $classMethod;
    /** @var ParameterExtractorInterface */
    private $parameterExtractor;
    /** @var Action */
    private $action;
    /** @var ContainerInterface */
    private $container;

    /**
     * ReflectionClassHandler constructor.
     * @param string                      $className
     * @param string                      $classMethod
     * @param ParameterExtractorInterface $parameterExtractor
     * @param Action                      $action
     * @param ContainerInterface          $container
     */
    public function __construct(string $className,
                                string $classMethod,
                                ParameterExtractorInterface $parameterExtractor,
                                Action $action,
                                ContainerInterface $container) {

        $this->className          = $className;
        $this->classMethod        = $classMethod;
        $this->parameterExtractor = $parameterExtractor;
        $this->action             = $action;
        $this->container          = $container;
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $class = null;
        if ($this->container->has($this->className)) {
            $class = $this->container->get($this->className);
        } else {
            $class = (new Injector($this->container))->create($this->className);
        }

        if (!method_exists($class, $this->classMethod)) {
            throw new HttpInternalServerErrorException('Could not find handler for request.');
        }

        $reflectionClass  = new ReflectionClass($class);
        $reflectionMethod = $reflectionClass->getMethod($this->classMethod);

        if ($reflectionMethod->getNumberOfParameters() === 0) {
            return $class->{$this->classMethod}();
        }

        $parsedParams = $this->parameterExtractor->getUriParameters(
            $this->action->getPattern(),
            $request->getRequestTarget()
        );


        $arguments = [];
        // The controller does not HAVE to have the required parameters, they are just required
        // in the uri, not the controller.
        // So check if it exists in the list of parameters, and if so, add it.
        foreach ($reflectionMethod->getParameters() as $parameter) {
            // If a parameter exists in the controller, it have to be set as long as it is not optional, in that case
            // we can set it to null.
            $name = mb_strtolower($parameter->getName());
            if ($parsedParams->has($name)) {
                $arguments[] = $parsedParams[$name];
            } else if ($parameter->isOptional()) {
                $arguments[] = null;
            } else {
                // In some cases, user want to pass the request to the handler, we can't expect them to use a certain
                // name, so instead, we force them to use a RequestInterface implementation as the parameter type.
                $c = null;
                try {
                    $c = $parameter->getClass();
                    if ($c && $c->implementsInterface(RequestInterface::class)) {
                        $arguments[] = $request;
                        continue;
                    }
                } catch (ReflectionException $ex) {
                    // Do nothing.
                }
                // Finally, if it's not a request interface, it should be thrown as a bad request, the argument does
                // not exist.
                throw new HttpBadRequestException(sprintf(
                        'Parameter in handler does not exist in pattern (%s).',
                        $parameter->getName()
                    )
                );
            }
        }

        return $class->{$this->classMethod}(...$arguments);
    }
}