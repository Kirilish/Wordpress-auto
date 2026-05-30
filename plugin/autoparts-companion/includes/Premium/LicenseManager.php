<?php

declare(strict_types=1);

namespace AutoParts\Premium;

final class LicenseManager
{
    public function register_hooks(): void
    {
        add_action('admin_post_autoparts_save_license', [$this, 'save']);
    }

    public function save(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Недостаточно прав.', 'autoparts-companion'));
        }
        check_admin_referer('autoparts_save_license');
        $key = sanitize_text_field(wp_unslash($_POST['license_key'] ?? ''));
        update_option('autoparts_license_key', $key, false);
        update_option('autoparts_license_plan', $key ? 'premium' : 'free', false);
        update_option('autoparts_license_status', 'active', false);
        wp_safe_redirect(admin_url('admin.php?page=autoparts-premium&license=saved'));
        exit;
    }
}
