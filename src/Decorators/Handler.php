<?php
/**
 * Hookable attribute class file.
 *
 * @package WP Utils
 * @subpackage Decorators
 */

namespace Oblak\WP\Decorators;

use Attribute;
use XWP\Hook\Decorators\Handler as New_Handler;

/**
 * Defines class as hookable - it will be automatically constructed on given hook with given priority.
 *
 * @deprecated 2.0.0 Use `XWP\Hook\Decorators\Handler` instead.
 */
#[Attribute( Attribute::TARGET_CLASS )]
class Handler extends New_Handler {
}
