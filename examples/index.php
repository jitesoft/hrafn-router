<?php
require_once __DIR__ . '/../vendor/autoload.php';

$router = new \Hrafn\Router\Router();
$router->getBuilder()->group('api', function(\Hrafn\Router\RouteBuilder $builder) {
   $builder->group('v1', function(\Hrafn\Router\RouteBuilder $builder) {
      $builder->get('test', function() {
          return new \GuzzleHttp\Psr7\Response(200, [
              'Content-Type' => 'application/json'
          ], json_encode(['did-it-work?' => 'YES IT DID!']));
      });
   });
}, []);

$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
$response = $router->handle($request);
header('Content-Type: application/json');
$response->getBody()->rewind();
echo $response->getBody()->getContents();
