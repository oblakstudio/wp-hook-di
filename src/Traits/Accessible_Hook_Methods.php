<?php
/**
 * Accessible_Hook_Methods trait file.
 *
 * @package WP Utils
 * @subpackage Traits
 */

namespace Oblak\WP\Traits;

use Oblak\WP\Decorators\Filter;

/**
 * Allows making private methods of a class accessible from the outside.
 */
trait Accessible_Hook_Methods {
    /**
     * Magic method to call private methods which are hooked.
     *
     * @param  string $name      Method name.
     * @param  array  $arguments Method arguments.
     * @return mixed
     *
     * @throws \BadMethodCallException If the method does not exist or is not hooked.
     */
    public function __call( string $name, array $arguments ) {
        $should_throw = static::check_access( $this, $name );

        if ( false !== $should_throw ) {
            throw new \BadMethodCallException( esc_html( $should_throw ) );
        }

        return is_callable( array( 'parent', '__call' ) ) ? parent::__call( $name, $arguments ) : ( $this->$name )( ...$arguments );
    }

    /**
     * Magic method to call private static methods which are hooked.
     *
     * @param  string $name      Method name.
     * @param  array  $arguments Method arguments.
     * @return mixed
     *
     * @throws \BadMethodCallException If the method does not exist or is not hooked.
     */
    public static function __callStatic( string $name, array $arguments ) {
        $should_throw = static::check_access( static::class, $name );

        if ( false !== $should_throw ) {
            throw new \BadMethodCallException( esc_html( $should_throw ) );
        }

        return static::$name( ...$arguments );
    }

    /**
     * Checks if a method is callable.
     *
     * @param  string|object $class_or_obj Class name or object.
     * @param  string        $method       Method name.
     * @return string|false
     */
    private static function check_access( string|object $class_or_obj, string $method ): string|false {
        $classname = is_object( $class_or_obj ) ? $class_or_obj::class : $class_or_obj;

        return match ( true ) {
            ! method_exists( $class_or_obj, $method ) => 'Call to undefined method ' . $classname . '::' . $method,
            ! static::is_valid_method( $classname, $method ) => 'Call to private method ' . $classname . '::' . $method,
            default => false,
        };
    }

    /**
     * Checks if a private / protected method is callable.
     *
     * @param  string $classname Class name.
     * @param  string $method    Method name.
     * @return bool
     */
    private static function is_valid_method( string $classname, string $method ): bool {
        return array_reduce(
            static::get_registered_hooks( $classname, $method ),
            fn( bool $c, string $hook ) => $c || doing_action( $hook ) || doing_filter( $hook ),
            false
        );
    }

    /**
     * Get the valid hooks for a class and method.
     *
     * @param  string $classname Class name.
     * @param  string $method    Method name.
     * @return array
     */
    private static function get_registered_hooks( string $classname, string $method ): array {
        return array_unique( wp_list_pluck( Filter::$registry[ $classname ][ $method ] ?? array(), 'tag' ) );
    }
}
