<?php
/**
 * Utility functions for the WP hooks.
 *
 * @package WP Utils
 */

use Oblak\WP\Decorators\Base_Hook;

use function Oblak\WP\Utils\class_uses_deep;

/**
 * Invokes all the hooked methods for a class or object.
 *
 * @param  object|string $obj The object or class name to invoke the hooked methods for.
 * @param  bool          $all Whether to invoke all the methods or only the ones in the specified class.
 */
function xwp_invoke_hooked_methods( object|string $obj, bool $all = false ) {
    $methods = xwp_get_hooked_methods( $obj, $all );

    foreach ( $methods as $method => $hook_data ) {
        foreach ( $hook_data['hooks'] as $hook ) {
            $hook->run_hook(
                array( $obj, $method ),
                $hook_data['args']
            );
		}
	}
}

/**
 * Get all the hooked methods for a class or object.
 *
 * @param  object|string $obj The object or class name to get the hooked methods for.
 * @param  bool          $all Whether to get all the methods or only the ones in the class.
 * @return array<string, array{hooks: array<int, Base_Hook>, args: int}>
 */
function xwp_get_hooked_methods( object|string $obj, bool $all = false ): array {
    $reflector = new ReflectionClass( $obj );
    $methods   = array_filter(
        $reflector->getMethods( xwp_get_hookable_method_types( $obj ) ),
        fn( $m ) => $all || $m->class === $reflector->getName()
    );

    return array_filter(
        wp_array_flatmap(
            $methods,
            fn( $m )=> array(
				$m->getName() => array(
					'hooks' => xwp_get_hook_decorators( $m ),
					'args'  => $m->getNumberOfParameters(),
				),
            ),
        ),
        fn( $m ) => $m['hooks']
    );
}

/**
 * Get hook decorators for a method.
 *
 * @param  ReflectionFunctionAbstract|ReflectionClass $thing Method to get the decorators for.
 * @param  string                                     $att   Decorator attribute to get.
 * @return array
 */
function xwp_get_hook_decorators(
    ReflectionFunctionAbstract|ReflectionClass $thing,
    string $att = Base_Hook::class
): array|false {
    $decorators = array_filter(
        array_map(
            fn( $d ) => $d?->newInstance() ?? false,
            $thing->getAttributes( $att, ReflectionAttribute::IS_INSTANCEOF )
        )
    );

    return $decorators ? $decorators : false;
}

/**
 * Get the method types that can be hooked.
 *
 * @param  object|string $obj The object or class name to get the method types for.
 * @return int                Bitmask of method types.
 */
function xwp_get_hookable_method_types( object|string $obj ): int {
	$method_types = ReflectionMethod::IS_PUBLIC;

	if ( is_string( $obj ) ) {
		$method_types |= ReflectionMethod::IS_STATIC;
	}

	if ( in_array( '\Oblak\WP\Traits\Accessible_Hook_Methods', class_uses_deep( $obj ), true ) ) {
		$method_types |= ReflectionMethod::IS_PRIVATE | ReflectionMethod::IS_PROTECTED;
	}

	return $method_types;
}
