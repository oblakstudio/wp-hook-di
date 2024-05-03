<?php //phpcs:disable SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalFunctions
/**
 * WordPress metadata utils
 *
 * @package WP Utils
 * @subpackage Utility functions
 */

namespace Oblak\WP\Utils;

use Oblak\WP\Annotation_Parser;

/**
 * Get decorators for a class or object.
 *
 * @template T
 * @param  string|object   $class_or_obj Class or object to get the decorators for.
 * @param  class-string<T> $decorator    Decorator class to get.
 * @param  bool            $all          Whether to get all the decorators or only the ones in the class.
 * @return T[]                           Array of decorators.
 *
 * @deprecated 2.0.0 Use Invoker API.
 */
function get_decorators( $class_or_obj, $decorator, bool $all = false ) {
    return $all
        ? \XWP\Hook\Reflection::get_decorators_deep( $class_or_obj, $decorator )
        : \XWP\Hook\Reflection::get_decorators( $class_or_obj, $decorator );
}

/**
 * Parses PHPDoc annotations.
 *
 * @param  \ReflectionMethod $method      Method to parse annotations for.
 * @param  array<string>     $needed_keys Keys that must be present in the parsed annotations.
 * @return array<string,string>           Parsed annotations.
 *
 * @deprecated 2.0.0 Use Invoker API.
 */
function parse_annotations( \ReflectionMethod &$method, ?array $needed_keys = null ): ?array {
    $annotations = Annotation_Parser::parse_annotations( $method->getDocComment(), $needed_keys );

    if ( ! $annotations ) {
        return null;
    }

    $annotations['priority'] = get_hook_priority( $annotations['priority'] ?? null );

    return $annotations;
}

/**
 * Determine the priority of a hook.
 *
 * @param  int|string|callable|null $priority_prop Priority property.
 * @return int
 *
 * @deprecated 2.0.0 Use Invoker API.
 */
function get_hook_priority( int|string|callable|null $priority_prop = null ): int {
    $p = Annotation_Parser::parse_priority( $priority_prop );

    return match ( true ) {
        is_callable( $p ) => $p(),
        is_string( $p )   => apply_filters( $p, 10 ),
        default         => $p,
    };
}

/**
 * Get all the hooks in public methods of a class
 *
 * @param  class-string|object $class_or_obj Class or object to get the hooks for.
 * @param  array|null          $needed_keys  Keys that must be present in the parsed annotations.
 * @param  bool                $all          Whether to get all the hooks or only the ones in the class.
 * @return array                             Array of hooks.
 *
 * @deprecated 2.0.0 Use Invoker API.
 */
function get_class_hooks( $class_or_obj, ?array $needed_keys = null, bool $all = false ): array {
    return Annotation_Parser::get_class_hooks( $class_or_obj, $needed_keys, $all );
}

/**
 * Invoke hooks for a class or object.
 *
 * @param  string|object $class_or_obj Class or object to invoke the hooks for.
 * @param  array|null    $hooks        Hooks to invoke.
 *
 * @deprecated 2.0.0 Use Invoker API.
 */
function invoke_class_hooks( $class_or_obj, ?array $hooks = null ) {
    Annotation_Parser::invoke_class_hooks( $class_or_obj, $hooks );
}
