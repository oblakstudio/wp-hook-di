<?php
/**
 * Action decorator class file.
 *
 * @package WP_Utils
 * @subpackage Decorators
 */

namespace Oblak\WP\Decorators;

use XWP\Hook\Decorators\Action as New_Action;

/**
 * Action decorator.
 *
 * @deprecated 1.0.0 Use `XWP\Hook\Decorators\Action` instead.
 */
#[\Attribute( \Attribute::TARGET_FUNCTION | \Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD )]
class Action extends New_Action {
}
