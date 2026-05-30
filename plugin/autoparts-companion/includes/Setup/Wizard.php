<?php

declare(strict_types=1);

namespace AutoParts\Setup;

final class Wizard
{
    public function register_hooks(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'redirect']);
        add_action('admin_post_autoparts_finish_wizard', [$this, 'finish']);
    }

    public function menu(): void
    {
        add_submenu_page('autoparts', 'Мастер запуска', 'Мастер запуска', 'manage_options', 'autoparts-wizard', [$this, 'render']);
    }

    public function redirect(): void
    {
        if (get_option('autoparts_wizard_pending') && current_user_can('manage_options') && ! wp_doing_ajax() && is_admin() && ! str_contains($_SERVER['REQUEST_URI'] ?? '', 'autoparts-wizard')) {
            delete_option('autoparts_wizard_pending');
            wp_safe_redirect(admin_url('admin.php?page=autoparts-wizard'));
            exit;
        }
    }

    public function render(): void
    {
        include AUTOPARTS_COMPANION_PATH . 'templates/admin-wizard.php';
    }

    public function finish(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Недостаточно прав.', 'autoparts-companion'));
        }
        check_admin_referer('autoparts_finish_wizard');
        Activator::create_pages();
        wp_safe_redirect(admin_url('admin.php?page=autoparts&wizard=done'));
        exit;
    }
}
