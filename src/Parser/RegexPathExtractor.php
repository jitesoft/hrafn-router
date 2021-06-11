<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RegexPathExtractor.php - Part of the router project.

  © - Jitesoft 2018-2021
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
 * @author  Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 *
 * Class used to extract parts from a path using regular expressions.
 *
 */
class RegexPathExtractor implements PathExtractorInterface {
    private LoggerInterface $logger;
    private string $delimiter;
    private string $optionalPattern;
    private string $requiredPattern;

    /**
     * RegexPathExtractor constructor.
     *
     * @param LoggerInterface $logger Logger to use.
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
     * @param LoggerInterface $logger Logger to use.
     * @return void
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger): void {
        $this->logger = $logger;
    }

    /**
     * Get parts of a path.
     * When a part is a defined parameter, it will be a string value named '%PARAM%'.
     *
     * @param string $path Path to extract uri parts from.
     * @return QueueInterface
     */
    public function getUriParts(string $path): QueueInterface {
        $this->logger->debug(
            '{tag} Extracting URI Paths from the supplied route path [{path}].',
            [
                'tag'  => Router::LOG_TAG,
                'path' => $path
            ]
        );
        $format  = '%s%s%s';
        $replace = [
            sprintf(
                $format,
                $this->delimiter,
                $this->requiredPattern,
                $this->delimiter
            ),
            sprintf(
                $format,
                $this->delimiter,
                $this->optionalPattern,
                $this->delimiter
            )
        ];

        $path = preg_replace($replace, '%PARAM%', $path);
        $list = explode('/', $path);
        $this->logger->debug(
            '{tag} Extracted {count} parts from the path.', [
                'tag'   => Router::LOG_TAG,
                'count' => count($list)
            ]
        );
        $queue = new LinkedQueue();
        if (empty($path) || $path === '/') {
            $this->logger->debug(
                '{tag} Path was empty or root.',
                ['tag' => Router::LOG_TAG]
            );
            $list = [''];
        } else {
            if ($list[0] === '') {
                $list = array_slice($list, 1);
            }
            if (count($list) !== 0 && $list[count($list) - 1] === '') {
                array_pop($list);
            }
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $queue->enqueue(...$list);
        return $queue;
    }

}
