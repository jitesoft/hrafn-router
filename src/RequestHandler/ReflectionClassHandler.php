<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ReflectionClassHandler.phpandler.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\RequestHandler;

use Hrafn\Router\Action;
use Hrafn\Router\Contracts\ParameterExtractorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

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

    /**
     * ReflectionClassHandler constructor.
     * @param string                      $className
     * @param string                      $classMethod
     * @param ParameterExtractorInterface $parameterExtractor
     * @param Action                      $action
     */
    public function __construct(string $className,
                                string $classMethod,
                                ParameterExtractorInterface $parameterExtractor,
                                Action $action) {

        $this->className          = $className;
        $this->classMethod        = $classMethod;
        $this->parameterExtractor = $parameterExtractor;
        $this->action             = $action;
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {





    }
}
