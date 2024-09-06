<?php
/**
 * Annotation_Parser class file.
 *
 * @package eXtended WordPress
 */

namespace Oblak\WP;

use Automattic\Jetpack\Constants;
use XWP\Contracts\Hook\Initializable;
use XWP\Contracts\Hook\Invokable;
use XWP\Hook\Decorators\Action;
use XWP\Hook\Decorators\Filter;

/**
 * Annotation parser for legacy code.
 */
final class Annotation_Parser {
    /**
     * Invoke hooks for a class or object.
     *
     * @template T of object
     * @param  T|Initializable<T>   $instance Class or object to invoke the hooks for.
     * @param  ?array<Invokable<T>> $hooks    Hooks to invoke.
     */
    public static function invoke_class_hooks( object $instance, ?array $hooks = null ) {
        $handler = \xwp_create_handler( $instance );

        if ( ! $handler->get_target() ) {
            $handler->with_target( $instance );
        }

        $hooks ??= self::get_class_hooks( $instance );
        $hooks   = self::create_hook_decorators( $handler, $hooks );

        \xwp_load_hooks( $handler, $hooks )->invoke_methods( $handler );
    }

    /**
     * Get all the hooks in public methods of a class
     *
     * @template T of object
     * @param  class-string<T>|T $instance Class or object to get the hooks for.
     * @param  ?array<string>    $keys     Keys that must be present in the parsed annotations.
     * @param  bool              $all      Whether to get all the hooks or only the ones in the class.
     * @return array<string,array<string,mixed>>
     */
    public static function get_class_hooks( string|object $instance, ?array $keys = null, bool $all = false ): array {
        $reflector = new \ReflectionClass( $instance );
        $classname = $reflector->getName();

        $methods = \array_filter(
            $reflector->getMethods( \ReflectionMethod::IS_PUBLIC ) ?? array(),
            static fn( $method ) => $all || $method->class === $classname,
        );
        $parsed  = array();

        foreach ( $methods as $m ) {
            $data = self::parse_annotations( $m->getDocComment(), $keys );

            if ( ! $data ) {
                continue;
            }

            $parsed[ $m->getName() ] = \wp_parse_args( $data, array( 'refl' => $m ) );
        }

        return $parsed;
    }

    /**
     * Create hook decorators from the hooks.
     *
     * @param  Initializable       $handler Handler to create the decorators for.
     * @param  array<string,array> $hooks    Hooks to create decorators for.
     * @return array<string,array<Invokable>>
     */
    public static function create_hook_decorators( Initializable $handler, array $hooks ): array {
        $decorators = array();
        $classes    = array(
            'action' => Action::class,
            'filter' => Filter::class,
        );

        foreach ( $hooks as $method => $data ) {
            $classn = $classes[ $data['type'] ] ?? null;

            if ( ! isset( $data['hook'] ) || ! $classn ) {
                continue;
            }

            $decorators[ $method ] = self::create_hook_decorator( $handler, $data, $method, $classn );
        }

        return $decorators;
    }

    /**
     * Create a Decorator for a hook.
     *
     * @param  Initializable               $handler Handler to create the decorator for.
     * @param  array                       $data    Hook data.
     * @param  string                      $method  Method name.
     * @param  class-string<Action|Filter> $cn      Decorator class name.
     * @return array<Action|Filter>                 Array of hook decorators.
     */
    private static function create_hook_decorator( Initializable $handler, array $data, string $method, string $cn ): array {
        $tags  = \array_map( 'trim', \explode( ',', $data['hook'] ) );
        $prios = \array_map( 'trim', \explode( ',', $data['priority'] ?? '' ) );
        $decs  = array();

        foreach ( $tags as $i => $tag ) {
            $prio = self::parse_priority( $prios[ $i ] ?? '' );

            $decs[] = ( new $cn( tag: $tag, priority: $prio ) )
                ->with_handler( $handler )
                ->with_target( $method )
                ->set_reflector( $data['refl'] );
        }

        return $decs;
    }

    /**
     * Parses the legacy priority format.
     *
     * @param  int|string|callable|null $p Priority to parse.
     * @return int|string|callable
     */
    public static function parse_priority( int|string|callable|null $p = null ): int|string|callable {
        $p ??= 10;

        return match ( true ) {
            \is_numeric( $p )                 => \abs( \intval( $p ) ),
            \is_callable( $p )                => $p,
            \str_starts_with( $p, 'filter:' ) => \str_replace( 'filter:', '', $p ),
            Constants::get_constant( $p )     => Constants::get_constant( $p ),
            default                           => 10,
        };
    }

    /**
     * Parses PHPDoc annotations.
     *
     * @param  string|false  $doc         Doc comment to parse.
     * @param  array<string> $needed_keys Keys that must be present in the parsed annotations.
     * @return array<string,string>       Parsed annotations.
     */
    public static function parse_annotations( string|false $doc, ?array $needed_keys ): array|false {
        if ( false === $doc ) {
            return false;
        }

        \preg_match_all( '/@([a-z]+?)\s+(.*?)\n/i', $doc, $annotations );

		if ( ! isset( $annotations[1] ) || 0 === \count( $annotations[1] ) ) {
			return false;
		}

		$needed_keys ??= array( 'hook', 'type' );

        $annotations = \array_filter(
            \array_combine(  // Combine the annotations with their values.
                \array_map( 'trim', $annotations[1] ), // Trim the keys.
                \array_map( 'trim', $annotations[2] ), // Trim the values.
            ),
            static fn( $v ) => '' !== $v,
		);

        $found = \xwp_array_slice_assoc( $annotations, ...$needed_keys );

        return \count( $found ) >= \count( $needed_keys )
            ? $annotations
            : false;
	}
}
