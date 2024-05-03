<?php //phpcs:disable SlevomatCodingStandard.Classes.SuperfluousTraitNaming.SuperfluousSuffix
/**
 * Base_Plugin class file.
 *
 * @package WP Utils
 * @subpackage Abstracts
 */

namespace Oblak\WP\Traits;

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
        \Oblak\WP\Annotation_Parser::invoke_class_hooks( $this );
    }

    /**
     * Initializes the dependency dlasses
     */
    public function init_dependencies() {
        \XWP\Hook\Invoker::instance()->register_handlers( ...$this->get_dependencies() );
    }
}
