<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ParameterPathExtractorInterface.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Contracts;

use Jitesoft\Utilities\DataStructures\Lists\IndexedListInterface;

/**
 * ParameterPathExtractorInterface
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 * @state Stable
 */
interface PathExtractorInterface {

    /**
     * Get parts of a path.
     * When a part is a defined parameter, it will be a string value named '%PARAM%'.
     *
     * @param string $path
     * @return IndexedListInterface
     */
    public function getUriParts(string $path): IndexedListInterface;

}
