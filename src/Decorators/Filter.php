<?php
/**
 * Filter decorator class file.
 *
 * @package WP Utils
 * @subpackage Decorators
 */

namespace Oblak\WP\Decorators;

use XWP\Hook\Decorators\Filter as New_Filter;

/**
 * Filter decorator.
 *
 * @deprecated 2.0.0 Use `XWP\Hook\Decorators\Filter` instead.
 */
#[\Attribute( \Attribute::TARGET_FUNCTION | \Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD )]
class Filter extends New_Filter {
}
