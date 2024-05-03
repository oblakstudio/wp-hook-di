<?php
/**
 * Annotation_Parser class file.
 *
 * @package eXtended WordPress
 */

namespace Oblak\WP;

use Automattic\Jetpack\Constants;
use XWP\Contracts\Hook\Hookable;
use XWP\Contracts\Hook\Initializable;
use XWP\Hook\Decorators\Action;
use XWP\Hook\Decorators\Filter;
use XWP\Hook\Decorators\Hook;

/**
 * Annotation parser for legacy code.
 *
 * @deprecated 1.0.0
 */
final class Annotation_Parser {
    /**
     * Invoke hooks for a class or object.
     *
     * @param  object     $class_or_obj Class or object to invoke the hooks for.
     * @param  array|null $hooks        Hooks to invoke.
     */
    public static function invoke_class_hooks( object $class_or_obj, ?array $hooks = null ) {
        $handler = \XWP\Hook\Invoker::instance()->create_handler( $class_or_obj );

        if ( ! $handler->target ) {
            $handler->set_target( $class_or_obj );
        }

        $hooks ??= self::get_class_hooks( $class_or_obj );
        $hooks   = self::create_hook_decorators( $hooks, $handler );

        \XWP\Hook\Invoker::instance()
            ->load_hooks( $handler, $hooks )
            ->invoke_methods( $handler );
    }

    /**
     * Get all the hooks in public methods of a class
     *
     * @param  class-string|object $class_or_obj Class or object to get the hooks for.
     * @param  array|null          $needed_keys  Keys that must be present in the parsed annotations.
     * @param  bool                $all          Whether to get all the hooks or only the ones in the class.
     * @return array                             Array of hooks.
     */
    public static function get_class_hooks( $class_or_obj, ?array $needed_keys = null, bool $all = false ): array {
        $reflector = new \ReflectionClass( $class_or_obj );
        $classname = $reflector->getName();

        $methods = \array_filter(
            $reflector->getMethods( \ReflectionMethod::IS_PUBLIC ) ?? array(),
            static fn( $method ) => $all || $method->class === $classname,
        );
        $parsed  = array();

        foreach ( $methods as $m ) {
            $data = self::parse_annotations( $m->getDocComment(), $needed_keys );

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
     * @param  array<string, array> $hooks Hooks to create decorators for.
     * @param  Initializable        $handler Handler to create the decorators for.
     * @return array<string, array<Hookable>> Array of hook decorators.
     */
    public static function create_hook_decorators( array $hooks, Initializable $handler ): array {
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
                ->set_handler( $handler )
                ->set_target( array( $handler->target, $method ) )
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

        return \count( \wp_array_slice_assoc( $annotations, $needed_keys ) ) >= \count( $needed_keys )
            ? $annotations
            : false;
	}
}
