<?php
/**
 * Hook_Runner class file
 *
 * @package WP_Utils
 * @subpackage Abstracts
 */

namespace Oblak\WP\Abstracts;

/**
 * Base hook runner.
 *
 * Runs all the hooks registered in the class.
 *
 * @deprecated 1.0.0 Not needed anymore.
 */
abstract class Hook_Caller {
    /**
     * Constructor
     */
    public function __construct() {
        \XWP\Hook\Invoker::instance()->load_handler( $this );
    }
}
