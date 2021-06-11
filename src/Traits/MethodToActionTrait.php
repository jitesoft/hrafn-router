<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  MethodToActionTrait.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Traits;

use Hrafn\Router\Method;

/**
 * MethodToActionTrait
 *
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 * @state Stable
 *
 * Trait which passes each http-method specific call to an abstract action method.
 * Classes using trait have to implement the action method and should use that one instead
 * of the specific http-method method.
 */
trait MethodToActionTrait {

    /**
     * Method which all the http-method specific methods forward data to.
     *
     * @param string          $method      Method for the given action.
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array|null      $middleWares Middlewares to use for the action.
     * @return static
     */
    abstract protected function action(string $method,
        string $pattern,
        string | callable $handler,
        ?array $middleWares = []
    ): static;

    /**
     * Create a get action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return static
     */
    public function get(string $pattern,
        string | callable $handler,
        array $middleWares = []): static {
        return $this->action(Method::GET, $pattern, $handler, $middleWares);
    }

    /**
     * Create a head action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return static
     */
    public function head(string $pattern,
        string | callable $handler,
        array $middleWares = []): static {
        return $this->action(Method::HEAD, $pattern, $handler, $middleWares);
    }

    /**
     * Create a post action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return static
     */
    public function post(string $pattern,
        string | callable $handler,
        array $middleWares = []): static {
        return $this->action(Method::POST, $pattern, $handler, $middleWares);
    }

    /**
     * Create a put action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return static
     */
    public function put(string $pattern,
        string | callable $handler,
        array $middleWares = []): static {
        return $this->action(Method::PUT, $pattern, $handler, $middleWares);
    }

    /**
     * Create a delete action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return static
     */
    public function delete(string $pattern,
        string | callable $handler,
        array $middleWares = []): static {
        return $this->action(Method::DELETE, $pattern, $handler, $middleWares);
    }

    /**
     * Create a connect action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return static
     */
    public function connect(string $pattern,
        string | callable $handler,
        array $middleWares = []): static {
        return $this->action(Method::CONNECT, $pattern, $handler, $middleWares);
    }

    /**
     * Create a options action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return static
     */
    public function options(string $pattern,
        string | callable $handler,
        array $middleWares = []): static {
        return $this->action(Method::OPTIONS, $pattern, $handler, $middleWares);
    }

    /**
     * Create a trace action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return static
     */
    public function trace(string $pattern,
        string | callable $handler,
        array $middleWares = []): static {
        return $this->action(Method::TRACE, $pattern, $handler, $middleWares);
    }

    /**
     * Create a patch action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return static
     */
    public function patch(string $pattern,
        string | callable $handler,
        array $middleWares = []): static {
        return $this->action(Method::PATCH, $pattern, $handler, $middleWares);
    }

}
