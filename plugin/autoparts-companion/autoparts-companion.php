<?php
/**
 * Plugin Name: AutoParts Companion
 * Description: Commercial companion plugin for AutoParts Premium theme: parts catalog, requests, import, notifications, REST API and premium feature flags.
 * Version: 1.0.0
 * Author: AutoParts Premium
 * Requires PHP: 8.1
 * Text Domain: autoparts-companion
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('AUTOPARTS_COMPANION_VERSION', '1.0.0');
define('AUTOPARTS_COMPANION_FILE', __FILE__);
define('AUTOPARTS_COMPANION_PATH', plugin_dir_path(__FILE__));
define('AUTOPARTS_COMPANION_URL', plugin_dir_url(__FILE__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'AutoParts\\';
    if (0 !== strpos($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = AUTOPARTS_COMPANION_PATH . 'includes/' . str_replace('\\', '/', $relative) . '.php';
    if (is_readable($file)) {
        require_once $file;
    }
});

register_activation_hook(__FILE__, static function (): void {
    AutoParts\Setup\Activator::activate();
});

add_action('plugins_loaded', static function (): void {
    AutoParts\Core::instance()->boot();
});

if (! function_exists('autoparts_can_use')) {
    function autoparts_can_use(string $feature): bool
    {
        return AutoParts\Premium\FeatureFlags::can_use($feature);
    }
}
