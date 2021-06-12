<?php

namespace Hrafn\Router\Attributes;

use Attribute;
use Hrafn\Router\Method;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;

/**
 * Class Action
 *
 * Attribute used on methods in classes that uses the Controller attribute.
 * In case a Action is defined, the Harfn router will automatically add it to the route tree.
 *
 * @package Hrafn\Router\Attributes
 *
 * @property {string} $path Optional path, which will be appended to the controller base path.
 * @property {string} $method Optional method, defaults to 'GET', which the action will be invoked on.
 *
 * @see Controller
 * @see Middleware
 */
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
class Action {
    /**
     * Path/pattern to use to resolve the action.
     *
     * @var string Action path.
     */
    public string $path;

    /**
     * Action request method.
     * @see Method
     * @var string Method the route uses.
     */
    public string $method;

    public function __construct(string $path = '/', string $method = "GET") {
        $this->path = $path;
        $this->method = $method;

        if (!in_array(strtolower($this->method), Method::getConstantValues())) {
            throw new InvalidArgumentException('Invalid route method. See Hrafn\Router\Method for allowed values.');
        }
    }
}
