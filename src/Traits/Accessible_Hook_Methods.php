<?php
/**
 * Accessible_Hook_Methods trait file.
 *
 * @package WP Utils
 * @subpackage Traits
 */

namespace Oblak\WP\Traits;

use XWP\Contracts\Hook\Accessible_Hook_Methods as AHM_Bleeding_Edge;

/**
 * Allows making private methods of a class accessible from the outside.
 */
trait Accessible_Hook_Methods {
    use AHM_Bleeding_Edge;
}
