<?php
namespace Oblak\WP\Interfaces;

interface Complex_Hook {
    /**
     * Initializes the hook.
     *
     * @return static
     */
    public static function init(): static;
}
