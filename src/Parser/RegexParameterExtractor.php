<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RegexParameterExtractor.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Parser;

use function array_key_exists;
use Hrafn\Router\Contracts\ParameterExtractorInterface;
use Hrafn\Router\Router;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use Jitesoft\Utilities\DataStructures\Maps\MapInterface;
use Jitesoft\Utilities\DataStructures\Maps\SimpleMap;
use Psr\Log\LoggerInterface;

/**
 * RegexParameterExtractor
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 *
 * Class used to extract parameters from a path by using regular expressions.
 *
 */
class RegexParameterExtractor implements ParameterExtractorInterface {
    /** @var LoggerInterface */
    private $logger;
    /** @var string  */
    private $delimiter;
    /** @var string  */
    private $optionalPattern;
    /** @var string */
    private $requiredPattern;

    /**
     * RegexParameterExtractor constructor.
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
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * @param string  $pattern  Pattern to extract names from.
     * @param boolean $optional If the parameter is optional or not.
     * @return array
     */
    private function getParameterNames(string $pattern,
                                       bool $optional = false): array {
        $count = preg_match_all(
            sprintf(
                '%s%s%s',
                $this->delimiter,
                (
                    $optional ? $this->optionalPattern : $this->requiredPattern
                ),
                $this->delimiter
            ),
            $pattern,
            $matches
        );

        if ($count > 0) {
            if ($optional) {
                return array_map(
                    function($value) {
                        return str_replace('?', '', $value);
                    },
                    $matches[1]
                );
            }
            return $matches[1];
        }
        return [];
    }

    /**
     * Get parameters from a path using a specified pattern.
     * Resulting map will be mapped as:
     *
     * Each parameter name have been converted to lower for ease of comparision.
     *
     * <code>
     * [
     *   parametername => parametervalue, ...
     * ]
     * </code>
     *
     * @param string $pattern Pattern to use.
     * @param string $path    Path to extract values from.
     * @return MapInterface
     * @throws InvalidArgumentException On invalid argument.
     */
    public function getUriParameters(string $pattern,
                                     string $path): MapInterface {
        $this->logger->debug(
            '{tag} Fetching parameters from given path.', [
                'tag' => Router::LOG_TAG
            ]
        );
        // Remove the trailing slash from the path if there is one.
        $path = rtrim($path, '/');

        // To fetch the url parameters with regex, we have to replace each placeholder and optional placeholder
        // with a regular expression string. And, of course, we use regex for that too!
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
            ),
            sprintf($format, $this->delimiter, '([/])', $this->delimiter)
        ];
        $with    = [
            "(?'$1'\w+)",      // Named capturing group for placeholder.
            "(?:(?'$1'\w+))?", // Named group (inside none-capturing) for optional placeholders.
            '\/'               // Slash should be escaped too!
        ];

        $regEx = preg_replace($replace, $with, $pattern);
        // We need to make sure that optional placeholders have an optional slash, to make sure that
        // `/path/optional`, /path/optional/ `/path/` and `/path` will match the regex.
        // Future optimization could use a lookahead to replace it in one pass.
        $regEx = preg_replace('~\\\/\(\?\:~', '[\/]?(?:', $regEx);
        // Put together the full regular expression string before testing.
        // The extra optional slash should be added so that trailing slashes are allowed.
        $regEx = sprintf(
            '%s^%s[\/]?$%s',
            $this->delimiter,
            $regEx,
            $this->delimiter
        );

        // Test the $path.
        preg_match_all($regEx, $path, $matches, 512);
        array_change_key_case($matches, 0);
        $optionalParameterNames = $this->getParameterNames($pattern, true);
        $requiredParameterNames = $this->getParameterNames($pattern, false);

        $outParameters = new SimpleMap();

        foreach ($requiredParameterNames as $name) {
            $name = mb_strtolower($name);
            if (!array_key_exists($name, $matches)
                || count($matches[$name]) <= 0
                || empty($matches[$name][0])
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Error matching pattern "%s" with path "%s".',
                        $pattern,
                        $path
                    )
                );
            }
            $outParameters->add($name, $matches[$name][0]);
        }

        foreach ($optionalParameterNames as $name) {
            if (!array_key_exists($name, $matches)) {
                continue;
            }
            $value = $matches[$name][0];
            if (empty($value)) {
                $value = null;
            }

            $outParameters->add(mb_strtolower($name), $value);
        }

        return $outParameters;
    }

}
