<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteBuilder.php - Part of the router project.

  © - Jitesoft 2018-2021
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router;

use Hrafn\Router\Contracts\ParameterExtractorInterface;
use Hrafn\Router\Contracts\PathExtractorInterface;
use Hrafn\Router\Contracts\RouteBuilderInterface;
use Hrafn\Router\RouteTree\Node;
use Hrafn\Router\RouteTree\RouteTreeManager;
use Hrafn\Router\Traits\MethodToActionTrait;
use Jitesoft\Utilities\DataStructures\Maps\MapInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * RouteBuilder
 *
 * @author  Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class RouteBuilder implements RouteBuilderInterface, LoggerAwareInterface {
    use MethodToActionTrait;

    public const LOG_TAG = 'Hrafn\RouteBuilder:';

    private Node $root;
    private array $middlewares;
    private PathExtractorInterface $extractor;
    private RouteTreeManager $manager;
    private string $basePattern;
    private LoggerInterface $logger;
    private MapInterface $actionContainer;
    private ParameterExtractorInterface $parameterExtractor;
    private ContainerInterface | MapInterface | null $container;

    /**
     * RouteBuilder constructor.
     *
     * @param array                                $middlewares        List of middlewares.
     * @param Node                                 $node               Root node.
     * @param PathExtractorInterface               $extractor          Path extractor object.
     * @param ParameterExtractorInterface          $parameterExtractor Parameter extractor object.
     * @param RouteTreeManager                     $manager            RouteTree manager.
     * @param string                               $basePattern        Base pattern of current node.
     * @param LoggerInterface                      $logger             Logger to use.
     * @param MapInterface                         $actionContainer    Container which actions is stored in.
     * @param ContainerInterface|MapInterface|null $container          Dependency container to use for injection.
     */
    public function __construct(
        array $middlewares,
        Node $node,
        PathExtractorInterface $extractor,
        ParameterExtractorInterface $parameterExtractor,
        RouteTreeManager $manager,
        string $basePattern,
        LoggerInterface $logger,
        MapInterface $actionContainer,
        ContainerInterface | MapInterface | null $container = null
    ) {
        $this->root               = $node;
        $this->middlewares        = $middlewares;
        $this->extractor          = $extractor;
        $this->manager            = $manager;
        $this->basePattern        = $this->cleanupPattern($basePattern);
        $this->actionContainer    = $actionContainer;
        $this->logger             = $logger;
        $this->parameterExtractor = $parameterExtractor;
        $this->container          = $container;
    }

    /**
     * @param string $pattern Pattern to clean up.
     * @return string
     */
    private function cleanupPattern(string $pattern): string {
        return trim($pattern, '/');
    }

    /**
     * @param string $pattern Pattern to create or fetch node for.
     * @return Node
     */
    private function getOrCreateNode(string $pattern): Node {
        $parts = $this->extractor->getUriParts($pattern);
        $node  = $this->manager->createOrGetNode(
            $this->root,
            $parts->dequeue()
        );

        while ($parts->count() > 0) {
            $node = $this->manager->createOrGetNode($node, $parts->dequeue());
        }
        $this->logger->debug(
            '{tag} Node fetched from the manager.',
            ['tag' => self::LOG_TAG]
        );

        return $node;
    }

    /**
     * Method which all the http-method specific methods forward data to.
     *
     * @param string          $method      Http method as string.
     * @param string          $pattern     Pattern used for the action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array|null      $middleWares Middlewares used for the specific action.
     * @return static
     */
    protected function action(string $method,
                              string $pattern,
                              string | callable $handler,
                              ?array $middleWares = []
    ): static {
        $pattern   = $this->cleanupPattern($pattern);
        $reference = sprintf(
            '%s::%s/%s',
            $method,
            $this->basePattern,
            $pattern
        );

        $node = $this->getOrCreateNode($pattern);

        $node->addReference($method, $reference);
        $this->actionContainer->set(
            $reference,
            new Action(
                $method,
                $handler,
                sprintf('%s/%s', $this->basePattern, $pattern),
                array_merge($this->middlewares, $middleWares),
                $this->parameterExtractor,
                $this->container
            )
        );
        $this->logger->debug(
            '{tag} Action added to node, reference generated successfully.',
            ['tag' => self::LOG_TAG]
        );

        return $this;
    }

    /**
     * Create a new namespace inside of current namespace.
     * A RouteBuilderInterface instance is passed as the single argument to the $closure callback.
     *
     * @param string     $pattern     Pattern for the namespace/group.
     * @param callable   $closure     Closure which will be passed the route builder.
     * @param array|null $middleWares Create a new route namespace/group.
     * @return static
     */
    public function namespace(
        string $pattern,
        callable $closure,
        ?array $middleWares = []
    ): static {
        $pattern = $this->cleanupPattern($pattern);

        $pathParts = $this->extractor->getUriParts($pattern);
        $node      = $this->root;
        $part      = $pathParts->dequeue();

        while ($part !== null) {
            $node = $this->manager->createOrGetNode($node, $part);
            $part = $pathParts->dequeue();
        }

        $builder = new RouteBuilder(
            array_merge($this->middlewares, $middleWares ?? []),
            $node,
            $this->extractor,
            $this->parameterExtractor,
            $this->manager,
            sprintf('%s/%s', $this->basePattern, $pattern),
            $this->logger,
            $this->actionContainer,
            $this->container
        );

        $closure($builder);
        return $this;
    }

    /**
     * Create a new group inside of current group.
     * A RouteBuilderInterface instance is passed as the single argument to the $closure callback.
     *
     * @alias namespace
     *
     * @param string     $pattern     Pattern for the namespace/group.
     * @param callable   $closure     Closure which will be passed the route builder.
     * @param array|null $middleWares Create a new route namespace/group.
     * @return static
     */
    public function group(
        string $pattern,
        callable $closure,
        array | null $middleWares = []
    ): static {
        return $this->namespace($pattern, $closure, $middleWares);
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger Logger to use.
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void {
        $this->logger = $logger;
    }

}
