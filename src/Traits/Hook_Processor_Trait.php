<?php //phpcs:disable SlevomatCodingStandard.Classes.SuperfluousTraitNaming.SuperfluousSuffix
/**
 * Base_Plugin class file.
 *
 * @package WP Utils
 * @subpackage Abstracts
 */

namespace Oblak\WP\Traits;

use Oblak\WP\Decorators\Hookable;

use function Oblak\WP\Utils\get_decorators;
use function Oblak\WP\Utils\invoke_class_hooks;

/**
 * Enables basic DI and hooking functionality for plugins / themes
 */
trait Hook_Processor_Trait {
    /**
     * Plugin textdomain
     *
     * @var string|null
     */
    protected ?string $textdomain = null;

    /**
     * Runs the hooks registered in the class, and initializes the dependencies.
     *
     * @param string $hook     Hook name.
     * @param int    $priority Hook priority.
     */
    public function init( string $hook = 'plugins_loaded', int $priority = 10 ) {
        \add_action( $hook, array( $this, 'run_hooks' ), $priority );
        \add_action( $hook, array( $this, 'init_dependencies' ), $priority );
    }

    /**
     * Return an array of class names to be instantiated on plugin init.
     *
     * @var array<int, class-string>
     */
    abstract protected function get_dependencies(): array;

    /**
     * Runs the registered hooks for the plugin.
     */
    public function run_hooks() {
        invoke_class_hooks( $this );
    }

    /**
     * Initializes the dependency dlasses
     */
    public function init_dependencies() {
        foreach ( $this->get_dependencies() as $dep_class ) {
            $dep = $this->get_dependency_data( $dep_class );

            if ( ! $dep || ! $dep['conditional']() ) {
                continue;
            }

            \add_action( $dep['hook'], static fn() => new $dep_class(), $dep['priority'] );
        }
    }

    /**
     * Get the dependency data from the class decorator
     *
     * @param  class-string $dep_class Dependency class name.
     * @return array|null              Dependency data.
     */
    protected function get_dependency_data( string $dep_class ): ?array {
        $metadata = get_decorators( $dep_class, Hookable::class );
        $metadata = \array_shift( $metadata );

        return $metadata ? array(
            'conditional' => $metadata->conditional ?? '__return_true',
            'hook'        => $metadata->hook,
            'priority'    => $metadata->priority,
        ) : null;
    }
}
