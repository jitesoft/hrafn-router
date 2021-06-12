<?php

namespace Hrafn\Router\Attributes;

use Attribute;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use Jitesoft\Utilities\DataStructures\Arrays;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;

/**
 * Class Middleware
 *
 * Attribute used on Controller and Actions to add a middleware.
 *
 * @package Hrafn\Router\Attributes
 *
 * @property {string} $fqn Class name to resolve. Must implement Psr/Http/Server/MiddlewareInterface
 *
 * @see Controller
 * @see Action
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Middleware {

    /**
     * @var string Class name.
     */
    public string $fqn;

    /**
     * Middleware constructor.
     *
     * @param string $fqn Class name to resolve.
     */
    public function __construct(string $fqn) {
        $this->fqn = $fqn;
        $class = new ReflectionClass($fqn);
        if (!$class->implementsInterface(MiddlewareInterface::class)) {
            throw new InvalidArgumentException('Middleware class must implement Psr/Http/Server/MiddlewareInterface.');
        }
    }

    public static function getMiddlewareName(string | object $class): string {
        $refClass = new ReflectionClass($class);
        $attributes = $refClass->getAttributes(static::class);
        $att = Arrays::first($attributes);
        return $att->getArguments()['fqn'];
    }

    public static function hasMiddleware(string | object $class): bool {
        $refClass = new ReflectionClass($class);
        $attributes = $refClass->getAttributes(static::class);
        return count($attributes) > 0;
    }

}
