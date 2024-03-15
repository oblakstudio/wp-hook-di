<?php
/**
 * Filter decorator class file.
 *
 * @package WP Utils
 * @subpackage Decorators
 */

namespace Oblak\WP\Decorators;

/**
 * Filter decorator.
 */
#[\Attribute( \Attribute::TARGET_FUNCTION | \Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD )]
class Filter extends Base_Hook {

    /**
     * {@inheritDoc}
     */
    protected function get_action(): callable {
        return 'add_filter';
    }
}
