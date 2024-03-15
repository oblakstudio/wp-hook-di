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
 */
abstract class Hook_Caller {
    /**
     * Constructor
     */
    public function __construct() {
        xwp_invoke_hooked_methods( $this );
    }
}
