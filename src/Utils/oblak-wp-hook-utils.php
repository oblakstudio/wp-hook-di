<?php //phpcs:disable Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition, SlevomatCodingStandard.ControlStructures.AssignmentInCondition.AssignmentInCondition
/**
 * Utility functions for the WP hooks.
 *
 * @package WP Utils
 */

use Oblak\WP\Decorators\Base_Hook;

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
            $hook->run_hook( array( $obj, $method ), $hook_data['args'] );
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
        static fn( $m ) => $all || $m->class === $reflector->getName()
    );

    return array_filter(
        wp_array_flatmap(
            static fn( $m ) => array(
				$m->getName() => array(
                    'args'  => $m->getNumberOfParameters(),
                    'hooks' => xwp_get_hook_decorators( $m ),
				),
            ),
            $methods,
        ),
        static fn( $m ) => $m['hooks']
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
    string $att = Base_Hook::class,
): array|false {
    $decorators = array_filter(
        array_map(
            static fn( $d ) => $d?->newInstance() ?? false,
            $thing->getAttributes( $att, ReflectionAttribute::IS_INSTANCEOF ),
        ),
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

	if ( in_array( 'Oblak\WP\Traits\Accessible_Hook_Methods', xwp_class_uses_deep( $obj ), true ) ) {
		$method_types |= ReflectionMethod::IS_PRIVATE | ReflectionMethod::IS_PROTECTED;
	}

	return $method_types;
}

/**
 * Get all the traits used by a class.
 *
 * @param  string|object $object_or_class Class or object to get the traits for.
 * @param  bool          $autoload        Whether to allow this function to load the class automatically through the __autoload() magic method.
 * @return array                          Array of traits.
 */
function xwp_class_uses_deep( string|object $object_or_class, bool $autoload = true ) {
    $traits = array();

    do {
        $traits = \array_merge( \class_uses( $object_or_class, $autoload ), $traits );
    } while ( $object_or_class = \get_parent_class( $object_or_class ) );

    foreach ( $traits as $trait ) {
        $traits = \array_merge( \class_uses( $trait, $autoload ), $traits );
    }

    return \array_values( \array_unique( $traits ) );
}
