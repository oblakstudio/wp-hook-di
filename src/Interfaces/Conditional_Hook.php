<?php
namespace Oblak\WP\Interfaces;

interface Conditional_Hook {
    public static function can_run(): bool;
}
