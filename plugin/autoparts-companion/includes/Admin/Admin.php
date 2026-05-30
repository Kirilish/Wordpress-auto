<?php

declare(strict_types=1);

namespace AutoParts\Admin;

use AutoParts\Meta;
use AutoParts\Premium\FeatureFlags;

final class Admin
{
    public function register_hooks(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('add_meta_boxes', [$this, 'metaboxes']);
        add_action('save_post_ap_part', [$this, 'save_part'], 10, 2);
        add_action('admin_post_autoparts_quick_add', [$this, 'quick_add']);
        add_action('admin_post_autoparts_save_settings', [$this, 'save_settings']);
    }

    public function menu(): void
    {
        add_menu_page('AutoParts', 'AutoParts', 'edit_posts', 'autoparts', [$this, 'dashboard'], 'dashicons-car', 26);
        add_submenu_page('autoparts', 'Dashboard', 'Dashboard', 'edit_posts', 'autoparts', [$this, 'dashboard']);
        add_submenu_page('autoparts', 'Запчасти', 'Запчасти', 'edit_posts', 'edit.php?post_type=ap_part');
        add_submenu_page('autoparts', 'Заявки', 'Заявки', 'edit_posts', 'edit.php?post_type=ap_request');
        add_submenu_page('autoparts', 'Быстро добавить', 'Быстро добавить', 'edit_posts', 'autoparts-quick-add', [$this, 'quick_add_page']);
        add_submenu_page('autoparts', 'Импорт CSV', 'Импорт', 'edit_posts', 'autoparts-import', [new ImportPage(), 'render']);
        add_submenu_page('autoparts', 'Бот и уведомления', 'Бот', 'manage_options', 'autoparts-bot', [$this, 'settings_page']);
        add_submenu_page('autoparts', 'Премиум', 'Премиум', 'manage_options', 'autoparts-premium', [$this, 'premium_page']);
    }

    public function assets(string $hook): void
    {
        if (str_contains($hook, 'autoparts') || get_post_type() === 'ap_part') {
            wp_enqueue_style('autoparts-admin', AUTOPARTS_COMPANION_URL . 'assets/css/admin.css', [], AUTOPARTS_COMPANION_VERSION);
        }
    }

    public function dashboard(): void
    {
        $parts = wp_count_posts('ap_part');
        $requests = wp_count_posts('ap_request');
        include AUTOPARTS_COMPANION_PATH . 'templates/admin-dashboard.php';
    }

    public function quick_add_page(): void
    {
        include AUTOPARTS_COMPANION_PATH . 'templates/admin-quick-add.php';
    }

    public function settings_page(): void
    {
        include AUTOPARTS_COMPANION_PATH . 'templates/admin-settings.php';
    }

    public function premium_page(): void
    {
        include AUTOPARTS_COMPANION_PATH . 'templates/admin-premium.php';
    }

    public function metaboxes(): void
    {
        add_meta_box('autoparts-part-data', __('Данные запчасти', 'autoparts-companion'), [$this, 'part_metabox'], 'ap_part', 'normal', 'high');
    }

    public function part_metabox(\WP_Post $post): void
    {
        wp_nonce_field('autoparts_save_part', 'autoparts_part_nonce');
        include AUTOPARTS_COMPANION_PATH . 'templates/admin-part-metabox.php';
    }

    public function save_part(int $post_id, \WP_Post $post): void
    {
        if (! isset($_POST['autoparts_part_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['autoparts_part_nonce'])), 'autoparts_save_part')) {
            return;
        }
        if (! current_user_can('edit_post', $post_id) || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        foreach (Meta::PART_FIELDS as $key => $type) {
            if (array_key_exists($key, $_POST)) {
                update_post_meta($post_id, $key, Meta::sanitize($type, wp_unslash($_POST[$key])));
            }
        }
    }

    public function quick_add(): void
    {
        if (! current_user_can('edit_posts')) {
            wp_die(esc_html__('Недостаточно прав.', 'autoparts-companion'));
        }
        check_admin_referer('autoparts_quick_add');
        $limit = FeatureFlags::limit('parts');
        if ($limit > 0 && (int) wp_count_posts('ap_part')->publish >= $limit) {
            wp_safe_redirect(admin_url('admin.php?page=autoparts-quick-add&limit=1'));
            exit;
        }
        $post_id = wp_insert_post([
            'post_type' => 'ap_part',
            'post_status' => 'publish',
            'post_title' => sanitize_text_field(wp_unslash($_POST['part_name'] ?? '')),
            'post_content' => sanitize_textarea_field(wp_unslash($_POST['description'] ?? '')),
        ], true);
        if (! is_wp_error($post_id)) {
            $map = ['_ap_brand' => 'brand', '_ap_model' => 'model', '_ap_oem' => 'oem', '_ap_price' => 'price', '_ap_seller_contact' => 'phone'];
            foreach ($map as $meta => $field) {
                update_post_meta((int) $post_id, $meta, sanitize_text_field(wp_unslash($_POST[$field] ?? '')));
            }
        }
        wp_safe_redirect(admin_url('admin.php?page=autoparts-quick-add&created=1'));
        exit;
    }

    public function save_settings(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Недостаточно прав.', 'autoparts-companion'));
        }
        check_admin_referer('autoparts_save_settings');
        $settings = [
            'store_name' => sanitize_text_field(wp_unslash($_POST['store_name'] ?? '')),
            'phone' => sanitize_text_field(wp_unslash($_POST['phone'] ?? '')),
            'email' => sanitize_email(wp_unslash($_POST['email'] ?? '')),
            'address' => sanitize_text_field(wp_unslash($_POST['address'] ?? '')),
            'currency' => sanitize_text_field(wp_unslash($_POST['currency'] ?? 'USD')),
            'telegram_enabled' => ! empty($_POST['telegram_enabled']),
            'telegram_token' => sanitize_text_field(wp_unslash($_POST['telegram_token'] ?? '')),
            'telegram_chat_id' => sanitize_text_field(wp_unslash($_POST['telegram_chat_id'] ?? '')),
            'telegram_template' => sanitize_textarea_field(wp_unslash($_POST['telegram_template'] ?? '')),
            'whatsapp_phone' => sanitize_text_field(wp_unslash($_POST['whatsapp_phone'] ?? '')),
            'viber_phone' => sanitize_text_field(wp_unslash($_POST['viber_phone'] ?? '')),
            'webhook_url' => esc_url_raw(wp_unslash($_POST['webhook_url'] ?? '')),
            'webhook_secret' => sanitize_text_field(wp_unslash($_POST['webhook_secret'] ?? '')),
        ];
        update_option('autoparts_settings', $settings, false);
        wp_safe_redirect(admin_url('admin.php?page=autoparts-bot&saved=1'));
        exit;
    }
}
