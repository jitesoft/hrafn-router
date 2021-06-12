<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Hrafn\Router\Attributes\Action;
use Hrafn\Router\Attributes\Controller;
use Hrafn\Router\Attributes\Middleware;
use Hrafn\Router\Router;
use Jitesoft\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ClassMiddleware implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $response = $handler->handle($request);
        $response->getBody()->rewind();
        $resJson = json_decode($response->getBody()->getContents(), true);
        $resJson['class-middleware'] = 'Was in middleware! (should be last in this case)';
        $response->getBody()->rewind();
        $response->getBody()->write(json_encode($resJson));
        return $response;
    }
}

class MethodMiddleware implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $response = $handler->handle($request);
        $response->getBody()->rewind();
        $resJson = json_decode($response->getBody()->getContents(), true);
        $resJson['method-middleware'] = 'Was in middleware! (should be second in this case)';
        $response->getBody()->rewind();
        $response->getBody()->write(json_encode($resJson));
        return $response;
    }
}

#[Controller('/api/v1')]
#[Middleware(ClassMiddleware::class)]
class TestController {

    #[Action('/test')]
    #[Middleware(MethodMiddleware::class)]
    public function getTest(): Response {
        return new Response(200, [
            'Content-Type' => 'application/json'
        ], json_encode(['did-it-work?' => 'YES IT DID!']));
    }
}

$router = new Router(new Container([
    MethodMiddleware::class => MethodMiddleware::class,
    ClassMiddleware::class => ClassMiddleware::class
]), useAttributes: true);

$request = ServerRequest::fromGlobals();
$response = $router->handle($request);
header('Content-Type: application/json');
$response->getBody()->rewind();
echo $response->getBody()->getContents();
