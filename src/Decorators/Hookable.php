<?php
/**
 * Hookable attribute class file.
 *
 * @package WP Utils
 * @subpackage Decorators
 */

namespace Oblak\WP\Decorators;

use Attribute;
use XWP\Hook\Decorators\Handler;

/**
 * Defines class as hookable - it will be automatically constructed on given hook with given priority.
 *
 * @deprecated 1.0.0 Use `XWP\Hook\Decorators\Handler` instead.
 */
#[Attribute( Attribute::TARGET_CLASS )]
class Hookable extends Handler {
    /**
     * Constructor
     *
     * @param  string   $hook        Hook name.
     * @param  int      $priority    Hook priority.
     * @param  callable $conditional Conditional callback function.
     * @param  mixed    ...$args     Arguments to pass to the hookable class constructor.
     */
    public function __construct(
        string $hook,
        int $priority = 10,
        $conditional = '__return_true',
        mixed ...$args,
    ) {
        parent::__construct( tag: $hook, priority: $priority, conditional: $conditional );
    }
}
