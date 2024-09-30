<?php //phpcs:disable Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition, SlevomatCodingStandard.ControlStructures.AssignmentInCondition.AssignmentInCondition
/**
 * Utility functions for the WP hooks.
 *
 * @package WP Utils
 */

use XWP\Contracts\Hook\Invokable;
use XWP\Hook\Reflection;

/**
 * Invokes all the hooked methods for a class or object.
 *
 * @param  object|string $obj The object or class name to invoke the hooked methods for.
 * @param  bool          $all Whether to invoke all the methods or only the ones in the specified class.
 *
 * @deprecated 2.0.0 Use Invoker API.
 */
function xwp_invoke_hooked_methods( object|string $obj, ?bool $all = null ) {
    if ( ! is_null( $all ) ) {
        _doing_it_wrong( __FUNCTION__, 'The $all parameter is deprecated.', '2.0.0' );
    }

    if ( ! is_object( $obj ) ) {
        $obj = new $obj();
    }

    xwp_load_handler( $obj );
}

/**
 * Get all the hooked methods for a class or object.
 *
 * @param  object|string $obj The object or class name to get the hooked methods for.
 * @param  ?bool         $all Whether to get all the methods or only the ones in the class.
 * @return array<string, array{hooks: array<int, Invokable>, args: int}>
 *
 * @deprecated 2.0.0 Use Invoker API.
 */
function xwp_get_hooked_methods( object|string $obj, ?bool $all = null ): array {
    if ( ! is_null( $all ) ) {
        _doing_it_wrong( __FUNCTION__, 'The $all parameter is deprecated.', '2.0.0' );
    }

    $methods = array();

    foreach ( Reflection::get_hookable_methods( Reflection::get_reflector( $obj ) ) as $m ) {
        $methods[ $m->getName() ] = array(
            'args'  => $m->getNumberOfParameters(),
            'hooks' => Reflection::get_decorators( $m, Invokable::class ),
        );
    }

    return $methods;
}

/**
 * Get hook decorators for a method.
 *
 * @param  Reflector $thing Method to get the decorators for.
 * @param  string    $att   Deprecated.
 * @return array
 *
 * @deprecated 2.0.0 Use Invoker API.
 */
function xwp_get_hook_decorators( Reflector $thing, string $att = '' ): array|false {
    if ( '' !== $att ) {
        _doing_it_wrong( __FUNCTION__, 'The $att parameter is deprecated.', '2.0.0' );
    }

    if ( ! ( $thing instanceof ReflectionClass ) ) {
        return false;
    }

    return Reflection::get_decorators( $thing, Invokable::class ) ?? false;
}

/**
 * Get the method types that can be hooked.
 *
 * @param  object|string $obj The object or class name to get the method types for.
 * @return int                Bitmask of method types.
 */
function xwp_get_hookable_method_types( object|string $obj ): int {
    return Reflection::get_method_types( xwp_class_uses_deep( $obj ) );
}

/**
 * Get all the traits used by a class.
 *
 * @param  string|object $object_or_class Class or object to get the traits for.
 * @param  bool          $autoload        Whether to allow this function to load the class automatically through the __autoload() magic method.
 * @return array                          Array of traits.
 *
 * @deprecated 2.0.0 Use Invoker API.
 */
function xwp_class_uses_deep( string|object $object_or_class, bool $autoload = true ) {
    return Reflection::class_uses_deep( $object_or_class, $autoload );
}
