<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RegexPathExtractor.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Parser;

use Hrafn\Router\Contracts\PathExtractorInterface;
use Hrafn\Router\Router;
use Jitesoft\Utilities\DataStructures\Queues\LinkedQueue;
use Jitesoft\Utilities\DataStructures\Queues\QueueInterface;
use Psr\Log\LoggerInterface;

/**
 * RegexPathExtractor
 *
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 *
 * Class used to extract parts from a path using regular expressions.
 *
 */
class RegexPathExtractor implements PathExtractorInterface {

    /** @var LoggerInterface */
    private $logger;
    /** @var string */
    private $delimiter;
    /** @var string  */
    private $optionalPattern;
    /** @var string */
    private $requiredPattern;

    /**
     * RegexPathExtractor constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger) {
        $this->logger          = $logger;
        $this->delimiter       = '~';
        $this->optionalPattern = '\{\?(\w+)\}';
        $this->requiredPattern = '\{(\w+?)\}';
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     * @return void
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * Get parts of a path.
     * When a part is a defined parameter, it will be a string value named '%PARAM%'.
     *
     * @param string $path
     * @return QueueInterface
     */
    public function getUriParts(string $path): QueueInterface {
        $this->logger->debug('{tag} Extracting URI Paths from the supplied route path.', [
            'tag' => Router::LOG_TAG
        ]);
        $format  = '%s%s%s';
        $replace = [
            sprintf($format, $this->delimiter, $this->requiredPattern, $this->delimiter),
            sprintf($format, $this->delimiter, $this->optionalPattern, $this->delimiter)
        ];

        $path = preg_replace($replace, '%PARAM%', $path);
        $list = explode('/', $path);
        $this->logger->debug('{tag} Extracted {count} parts from the path.', [
            'tag' => Router::LOG_TAG,
            'count' => count($list)
        ]);

        if ($list[0] === '') {
            $list = array_slice($list, 1);
        }
        if ($list[count($list)-1] === '') {
            array_pop($list);
        }

        $queue = new LinkedQueue();
        $queue->enqueue(...$list);
        return $queue;
    }

}
