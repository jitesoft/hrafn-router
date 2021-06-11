<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteParserInterface.php - Part of the router project.

  © - Jitesoft 2018-2021
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Contracts;

use Jitesoft\Utilities\DataStructures\Maps\MapInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * Interface used for parameter and path extraction.
 *
 * RouteParserInterface
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
interface ParameterExtractorInterface extends LoggerAwareInterface {

    /**
     * Get parameters from a path using a specified pattern.
     * Resulting map will be mapped as:
     *
     * <code>
     * parameterName => parameterValue
     * </code>
     *
     * @param string $pattern Pattern to extract params with.
     * @param string $path    Path to extract params from.
     * @return MapInterface
     */
    public function getUriParameters(string $pattern,
                                     string $path): MapInterface;

}
