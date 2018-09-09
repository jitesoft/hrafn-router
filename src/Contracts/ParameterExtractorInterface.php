<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteParserInterface.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Contracts;

use Jitesoft\Utilities\DataStructures\Lists\IndexedListInterface;
use Jitesoft\Utilities\DataStructures\Maps\MapInterface;

/**
 * Interface used for parameter and path extraction.
 *
 * RouteParserInterface
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 * @state Stable
 */
interface ParameterExtractorInterface {


    /**
     * Get parameters from a path using a specified pattern.
     * Resulting map will be mapped as:
     *
     * <code>
     * parameterName => parameterValue
     * </code>
     *
     * @param string $pattern
     * @param string $path
     * @return MapInterface
     */
    public function getUriParameters(string $pattern, string $path): MapInterface;

}
