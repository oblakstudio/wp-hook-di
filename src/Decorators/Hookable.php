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
 * @deprecated 2.0.0 Use `XWP\Hook\Decorators\Handler` instead.
 */
#[Attribute( Attribute::TARGET_CLASS )]
class Hookable extends Handler {
    /**
     * Constructor
     *
     * @param  string   $hook        Hook name.
     * @param  int      $priority    Hook priority.
     * @param  callable $conditional Conditional callback function.
     * @param  string   $tag         Tag name. Optional, if not provided, hook name will be used.
     * @param  mixed    ...$args     Arguments to pass to the hookable class constructor.
     *
     * @throws \InvalidArgumentException If hook or tag is not provided.
     */
    public function __construct(
        ?string $hook = null,
        int $priority = 10,
        $conditional = null,
        ?string $tag = null,
        mixed ...$args,
    ) {
        $tgt = $tag ?? $hook ?? false;

        if ( ! $tgt ) {
            throw new \InvalidArgumentException( 'Hook name or tag must be provided.' );
        }
        parent::__construct( tag: $tgt, priority: $priority, conditional: $conditional );
    }
}
