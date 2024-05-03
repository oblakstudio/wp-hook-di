<?php
/**
 * Function loader for the package.
 *
 * @package WP Hook DI
 */

if ( ! \function_exists( '\Oblak\WP\Utils\invoke_class_hooks' ) ) {
    require_once __DIR__ . '/oblak-wp-metadata-utils.php';
}

if ( ! \function_exists( 'xwp_invoke_hooked_methods' ) ) {
    require_once __DIR__ . '/oblak-wp-hook-utils.php';
}
