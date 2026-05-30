<?php

declare(strict_types=1);

namespace AutoPartsPremium;

final class Theme
{
    public static function setup(): void
    {
        load_theme_textdomain('autoparts-premium', get_template_directory() . '/languages');
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('custom-logo');
        add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
        add_theme_support('woocommerce');
        register_nav_menus(['primary' => __('Главное меню', 'autoparts-premium'), 'mobile' => __('Мобильное меню', 'autoparts-premium')]);
    }

    public static function assets(): void
    {
        wp_enqueue_style('autoparts-premium', get_template_directory_uri() . '/assets/css/theme.css', [], AUTOPARTS_THEME_VERSION);
        wp_enqueue_script('autoparts-premium', get_template_directory_uri() . '/assets/js/theme.js', [], AUTOPARTS_THEME_VERSION, true);
        $css = ':root{--ap-primary:' . esc_html(get_theme_mod('ap_primary_color', '#f59e0b')) . ';--ap-accent:' . esc_html(get_theme_mod('ap_accent_color', '#2563eb')) . ';--ap-radius:' . absint(get_theme_mod('ap_radius', 22)) . 'px;}';
        wp_add_inline_style('autoparts-premium', $css);
    }
}
