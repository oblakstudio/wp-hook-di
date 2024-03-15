<?php
/**
 *  Base_Hook class file.
 *
 * @package WP_Utils
 * @subpackage Decorators
 */

namespace Oblak\WP\Decorators;

/**
 * Base hook from which the action and filter decorators inherit.
 */
abstract class Base_Hook {
    /**
     * Array of registry/actions registered in the class.
     *
     * @var array<string, array<string, string>>
     */
    public static array $registry = array();

    /**
     * Hook priority
     *
     * @var int
     */
    private int $priority;

    /**
     * Constructor
     *
     * @param  string                    $tag      Hook tag.
     * @param  array|int|string|callable $priority Hook priority.
     */
    public function __construct(
        /**
         * The name of the action to which the function is hooked.
         *
         * @var string
         */
        private string $tag,
        array|int|string $priority = 10,
    ) {
        $this->priority = $this->parse_priority( $priority );

        static::$registry[ $tag ]                    ??= array();
        static::$registry[ $tag ][ $this->priority ] ??= array();
    }

    /**
     * Parse the priority
     *
     * @param  array|int|string|callable $prio Priority to parse.
     * @return int
     */
    private function parse_priority( array|int|string $prio ): int {
        return match ( true ) {
            is_numeric( $prio )      => (int) $prio,
            is_array( $prio )        => call_user_func( $prio ),
            is_callable( $prio )     => $prio(),
            is_string( $prio )       => apply_filters( $prio, 10, $this->tag ),
            default                  => 10,
        };
    }

    /**
     * Run the hook
     *
     * @param  callable $fn_to_add Function to add to the hook.
     * @param  int      $num_args  Number of arguments the function accepts.
     */
    public function run_hook( callable $fn_to_add, int $num_args = 1 ) {
        ( $this->get_action() )( $this->tag, $fn_to_add, $this->priority, $num_args );

        $this->add_to_registry( ...( (array) $fn_to_add ) );
    }

    /**
     * Get the action to run.
     *
     * @return callable
     */
    abstract protected function get_action(): callable;

    /**
     * Adds the callback to the registry.
     *
     * @param object|string ...$args The class or the method.
     */
    private function add_to_registry( mixed ...$args ) {
        $method = $args[1] ?? $args[0];
        $class  = isset( $args[1] ) ? $args[0]::class : 'function';

        static::$registry[ $class ] ??= array();

        static::$registry[ $class ][ $method ] ??= array();
        static::$registry[ $class ][ $method ][] = array(
            'tag'      => $this->tag,
            'priority' => $this->priority,
        );
    }
}
