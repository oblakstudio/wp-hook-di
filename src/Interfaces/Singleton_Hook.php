<?php
namespace Oblak\WP\Interfaces;

interface Singleton_Hook {
    /**
     * Returns the instance of the class.
     *
     * @return static
     */
    public static function instance(): static;
}
