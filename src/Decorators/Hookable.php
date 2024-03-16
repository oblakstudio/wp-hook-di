<?php
/**
 * Hookable attribute class file.
 *
 * @package WP Utils
 * @subpackage Decorators
 */

namespace Oblak\WP\Decorators;

use Attribute;
use Oblak\WP\Interfaces\Conditional_Hook;

/**
 * Defines class as hookable - it will be automatically constructed on given hook with given priority.
 */
#[Attribute( Attribute::TARGET_CLASS )]
class Hookable {
    /**
     * Array of hooks registered in all instances of the class.
     *
     * @var array<string, array<int, array<int, class-string<Conditional_Hook>>>>
     */
    protected static array $hooks = array();

    /**
     * Constructor
     *
     * @param  string $hook        Hook name.
     * @param  int    $priority    Hook priority.
     */
    public function __construct(
        /**
         * Hook name
         *
         * @var string
         */
        public string $hook,
        /**
         * Hook priority
         *
         * @var int
         */
        public int $priority = 10,
    ) {
        if ( $this->hook_registered() ) {
            return;
        }

        \add_action( $hook, array( $this, 'init_hook_classes' ), $priority );
    }

    protected function hook_registered(): bool {
        return isset( static::$hooks[ $this->hook ][ $this->priority ] );
    }

    public function init_hook_classes() {
        $a = static::$hooks[ $this->hook ][ $this->priority ];
        foreach ( static::$hooks[ $this->hook ][ $this->priority ] as $cn ) {
            $this->needs_class( $cn ) && $this->init_class( $cn );
        }
    }

    /**
     * Checks if a class should be instantiated.
     *
     * @param string $cn Class name.
     * @phan-param Conditional_Hook $name
     */
    protected function needs_class( string $cn ): bool {
        $has_check = $this->hook_implements( $cn, Conditional_Hook::class );
        return ! $has_check || ( $has_check && $cn::can_run() );
    }

    /**
     * Checks if a hookable class implements a given interface.
     *
     * @param  string $classname Class name.
     * @param  string $needs     Interface name.
     * @return bool
     */
    protected function hook_implements( string $classname, string $needs ): bool {
        return \in_array( $needs, \class_implements( $classname ), true );
    }
}
