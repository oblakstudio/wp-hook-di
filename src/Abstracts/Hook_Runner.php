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
abstract class Hook_Runner {
    /**
     * Constructor
     */
    public function __construct() {
        \Oblak\WP\Annotation_Parser::invoke_class_hooks( $this );
    }
}
