<?php

declare(strict_types=1);

namespace AutoParts\Frontend;

final class Shortcodes
{
    public function register_hooks(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_shortcode('autoparts_catalog', [$this, 'catalog']);
        add_shortcode('autoparts_search', [$this, 'search']);
        add_shortcode('autoparts_request_form', [$this, 'request_form']);
        add_shortcode('autoparts_garage', [$this, 'garage']);
        add_shortcode('autoparts_favorites', [$this, 'favorites']);
        add_shortcode('autoparts_cta', [$this, 'cta']);
    }

    public function assets(): void
    {
        wp_register_script('autoparts-frontend', AUTOPARTS_COMPANION_URL . 'assets/js/frontend.js', [], AUTOPARTS_COMPANION_VERSION, true);
        wp_localize_script('autoparts-frontend', 'AutoPartsApi', ['root' => esc_url_raw(rest_url('autoparts/v1')), 'nonce' => wp_create_nonce('wp_rest')]);
        wp_register_style('autoparts-frontend', AUTOPARTS_COMPANION_URL . 'assets/css/frontend.css', [], AUTOPARTS_COMPANION_VERSION);
    }

    public function catalog(array $atts = []): string
    {
        wp_enqueue_script('autoparts-frontend');
        wp_enqueue_style('autoparts-frontend');
        ob_start();
        include AUTOPARTS_COMPANION_PATH . 'templates/shortcode-catalog.php';
        return (string) ob_get_clean();
    }

    public function search(): string
    {
        wp_enqueue_script('autoparts-frontend');
        return '<form class="ap-search" action="' . esc_url(get_post_type_archive_link('ap_part')) . '"><input name="search" placeholder="OEM, название или авто"/><button>Найти</button></form>';
    }

    public function request_form(array $atts = []): string
    {
        wp_enqueue_script('autoparts-frontend');
        $part_id = absint($atts['part_id'] ?? get_the_ID());
        ob_start();
        include AUTOPARTS_COMPANION_PATH . 'templates/shortcode-request-form.php';
        return (string) ob_get_clean();
    }

    public function garage(): string
    {
        return '<section class="ap-panel"><h2>Мой гараж</h2><p>Добавьте авто, чтобы видеть совместимые детали. В Premium доступно несколько автомобилей.</p><form class="ap-garage-form"><input placeholder="Марка"><input placeholder="Модель"><input placeholder="Год"><button type="button">Сохранить авто</button></form></section>';
    }

    public function favorites(): string
    {
        return '<section class="ap-panel"><h2>Избранное</h2><div data-ap-favorites>Сохранённые детали появятся здесь.</div></section>';
    }

    public function cta(): string
    {
        return '<section class="ap-cta"><h2>Не нашли запчасть?</h2><p>Оставьте заявку — менеджер проверит склад и аналоги.</p>' . do_shortcode('[autoparts_request_form part_id="0"]') . '</section>';
    }
}
