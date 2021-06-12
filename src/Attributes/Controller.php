<?php
namespace Hrafn\Router\Attributes;

use Attribute;

/**
 * Class Controller
 *
 * Attribute used to auto resolve classes as controllers in the Hrafn router.
 *
 * @package Hrafn\Router\Attributes
 *
 * @property {string} $path Path to prepend to actions in the class.
 *
 * @see Action
 * @see Middleware
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Controller {

    /**
     * @var string Path to prepend to all actions in the class.
     */
    public string $path;

    /**
     * Controller constructor.
     *
     * @param string $path Optional path to prepend to all actions.
     */
    public function __construct(string $path = '/') {
        $this->path = $path;
    }
}