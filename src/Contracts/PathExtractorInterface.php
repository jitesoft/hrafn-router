<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ParameterPathExtractorInterface.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Contracts;

use Jitesoft\Utilities\DataStructures\Queues\QueueInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * ParameterPathExtractorInterface
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
interface PathExtractorInterface extends LoggerAwareInterface {

    /**
     * Get parts of a path.
     * When a part is a defined parameter, it will be a string value named '%PARAM%'.
     *
     * @param string $path
     * @return QueueInterface
     */
    public function getUriParts(string $path): QueueInterface;

}
