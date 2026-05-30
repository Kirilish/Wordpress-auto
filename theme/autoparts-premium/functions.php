<?php
/** AutoParts Premium theme bootstrap. */
declare(strict_types=1);

if (! defined('ABSPATH')) { exit; }

define('AUTOPARTS_THEME_VERSION', '1.0.0');
define('AUTOPARTS_THEME_PATH', get_template_directory());
define('AUTOPARTS_THEME_URL', get_template_directory_uri());

require_once AUTOPARTS_THEME_PATH . '/inc/Theme.php';
require_once AUTOPARTS_THEME_PATH . '/inc/Customizer.php';

add_action('after_setup_theme', [AutoPartsPremium\Theme::class, 'setup']);
add_action('wp_enqueue_scripts', [AutoPartsPremium\Theme::class, 'assets']);
add_action('customize_register', [AutoPartsPremium\Customizer::class, 'register']);
