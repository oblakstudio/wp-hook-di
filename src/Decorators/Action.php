<?php
/**
 * Action decorator class file.
 *
 * @package WP_Utils
 * @subpackage Decorators
 */

namespace Oblak\WP\Decorators;

/**
 * Action decorator.
 */
#[\Attribute( \Attribute::TARGET_FUNCTION | \Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD )]
class Action extends Base_Hook {

    /**
     * {@inheritDoc}
     */
    protected function get_action(): callable {
        return 'add_action';
    }
}
