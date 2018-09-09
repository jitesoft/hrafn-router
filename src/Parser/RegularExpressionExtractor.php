<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RegularExpressionParser.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Parser;

use Hrafn\Router\Contracts\ParameterExtractorInterface;
use Hrafn\Router\Contracts\PathExtractorInterface;
use Hrafn\Router\Router;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use Jitesoft\Utilities\DataStructures\Lists\IndexedList;
use Jitesoft\Utilities\DataStructures\Lists\IndexedListInterface;
use Jitesoft\Utilities\DataStructures\Maps\MapInterface;
use Jitesoft\Utilities\DataStructures\Maps\SimpleMap;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * RegularExpressionParser
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class RegularExpressionExtractor implements ParameterExtractorInterface, PathExtractorInterface, LoggerAwareInterface {

    private $placeholderPattern;
    private $optionalPlaceholderPattern;
    private $regexDelimiter;
    /** @var null|LoggerInterface */
    private $logger;

    /**
     * RegularExpressionExtractor constructor.
     * @param string          $placeholderPattern
     * @param string          $optionalPlaceholderPattern
     * @param string          $regexDelimiter
     * @param LoggerInterface $logger
     */
    public function __construct(string $placeholderPattern = '\{(\w+?)\}',
                                string $optionalPlaceholderPattern = '\{\?(\w+)\}',
                                string $regexDelimiter = '~',
                                ?LoggerInterface $logger = null) {

        $this->placeholderPattern         = $placeholderPattern;
        $this->optionalPlaceholderPattern = $optionalPlaceholderPattern;
        $this->regexDelimiter             = $regexDelimiter;
        $this->logger                     = $logger ?? new NullLogger();
    }

    public function getUriParts(string $path): IndexedListInterface {
        $this->logger->debug('{tag} Extracting URI Paths from the supplied route path.', [
            'tag' => Router::LOG_TAG
        ]);
        $format  = '%s%s%s';
        $replace = [
            sprintf($format, $this->regexDelimiter, $this->placeholderPattern, $this->regexDelimiter),
            sprintf($format, $this->regexDelimiter, $this->optionalPlaceholderPattern, $this->regexDelimiter)
        ];

        $path = preg_replace($replace, '%$1%', $path);
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

        return new IndexedList($list);
    }

    private function getOptionalParameterNames(string $pattern): array {
        $count = preg_match_all(sprintf(
            '%s%s%s',
            $this->regexDelimiter,
            $this->optionalPlaceholderPattern,
            $this->regexDelimiter
        ), $pattern, $matches);

        if ($count > 0) {
            return array_map(function($value) {
                return str_replace('?', '', $value);
            }, $matches[1]);
        }
        return [];
    }

    private function getRequiredParameterNames(string $pattern): array {
        $count = preg_match_all(sprintf(
            '%s%s%s',
            $this->regexDelimiter,
            $this->placeholderPattern,
            $this->regexDelimiter
        ), $pattern, $matches);

        if ($count > 0) {
            return $matches[1];
        }
        return [];
    }

    /**
     * @param string $pattern
     * @param string $path
     * @return MapInterface
     * @throws InvalidArgumentException
     */
    public function getUriParameters(string $pattern, string $path): MapInterface {
        $this->logger->debug('{tag} Fetching parameters from given path.', ['tag' => Router::LOG_TAG]);
        // Remove the trailing slash from the path if there is one.
        $path = mb_strrpos($path, '/') === mb_strlen($path) ? rtrim($path,'/') : $path;


        // To fetch the url parameters with regex, we have to replace each placeholder and optional placeholder
        // with a regular expression string. And, of course, we use regex for that too!
        $format  = '%s%s%s';
        $replace = [
            sprintf($format, $this->regexDelimiter, $this->placeholderPattern, $this->regexDelimiter),
            sprintf($format, $this->regexDelimiter, $this->optionalPlaceholderPattern, $this->regexDelimiter),
            sprintf($format, $this->regexDelimiter, '([/])', $this->regexDelimiter)
        ];
        $with    = [
            "(?'$1'\w+)",      // Named capturing group for placeholder.
            "(?:(?'$1'\w+))?", // Named group (inside none-capturing) for optional placeholders.
            '\/'               // Slash should be escaped too!
        ];

        $regEx = preg_replace($replace, $with, $pattern);
        // We need to make sure that optional placeholders have an optional slash, to make sure that
        // `/path/optional`, /path/optional/ `/path/` and `/path` will match the regex.
        // TODO: Future optimization could use a lookahead to replace it in one pass.
        $regEx = preg_replace('~\\\/\(\?\:~', '[\/]?(?:', $regEx);
        // Put together the full regular expression string before testing.
        // The extra optional slash should be added so that trailing slashes are allowed.
        $regEx = sprintf('%s^%s[\/]?$%s', $this->regexDelimiter, "{$regEx}", $this->regexDelimiter);
        // Test the $path
        preg_match_all($regEx, $path, $matches, 512 & 1);
        $map = new SimpleMap();

        $optionalParameterNames = $this->getOptionalParameterNames($pattern);
        $requiredParameterNames = $this->getRequiredParameterNames($pattern);

        foreach ($requiredParameterNames as $name) {
            if (!array_key_exists($name, $matches) || count($matches[$name]) <= 0 || empty($matches[$name][0])) {
                throw new InvalidArgumentException(sprintf(
                    'Error when trying to match pattern "%s" with path "%s", Could not match all parameters.',
                    $pattern,
                    $path
                ));
            }
            $map->add($name, $matches[$name][0]);
        }

        foreach ($optionalParameterNames as $name) {
            $map->add($name, array_key_exists($name, $matches) ? $matches[$name][0] : null);
        }

        return $map;
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
}
