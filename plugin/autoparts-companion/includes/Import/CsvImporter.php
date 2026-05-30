<?php

declare(strict_types=1);

namespace AutoParts\Import;

use AutoParts\Meta;

final class CsvImporter
{
    public function register_hooks(): void
    {
        add_action('admin_post_autoparts_csv_import', [$this, 'handle']);
    }

    public function handle(): void
    {
        if (! current_user_can('edit_posts')) {
            wp_die(esc_html__('Недостаточно прав.', 'autoparts-companion'));
        }
        check_admin_referer('autoparts_csv_import');
        if (empty($_FILES['csv']['tmp_name'])) {
            wp_safe_redirect(admin_url('admin.php?page=autoparts-import&error=empty'));
            exit;
        }

        $file = sanitize_text_field(wp_unslash($_FILES['csv']['tmp_name']));
        $handle = fopen($file, 'r');
        if (! $handle) {
            wp_safe_redirect(admin_url('admin.php?page=autoparts-import&error=open'));
            exit;
        }

        $headers = fgetcsv($handle);
        $created = 0;
        $updated = 0;
        $batch = wp_generate_uuid4();
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers ?: [], $row) ?: [];
            $external_id = sanitize_text_field((string) ($data['external_id'] ?? ''));
            $existing = $external_id ? get_posts(['post_type' => 'ap_part', 'meta_key' => '_ap_external_id', 'meta_value' => $external_id, 'fields' => 'ids', 'posts_per_page' => 1]) : [];
            $postarr = [
                'post_type' => 'ap_part',
                'post_status' => 'publish',
                'post_title' => sanitize_text_field((string) ($data['title'] ?? $data['name'] ?? 'Запчасть')),
                'post_content' => sanitize_textarea_field((string) ($data['description'] ?? '')),
            ];
            if ($existing) {
                $postarr['ID'] = (int) $existing[0];
                $post_id = wp_update_post($postarr, true);
                $updated++;
            } else {
                $post_id = wp_insert_post($postarr, true);
                $created++;
            }
            if (! is_wp_error($post_id)) {
                $meta_map = ['brand' => '_ap_brand', 'model' => '_ap_model', 'generation' => '_ap_generation', 'oem' => '_ap_oem', 'price' => '_ap_price', 'currency' => '_ap_currency', 'condition' => '_ap_condition', 'stock' => '_ap_stock_status', 'warehouse' => '_ap_warehouse', 'external_id' => '_ap_external_id'];
                foreach ($meta_map as $csv => $meta) {
                    if (isset($data[$csv])) {
                        update_post_meta((int) $post_id, $meta, Meta::sanitize(Meta::PART_FIELDS[$meta] ?? 'text', $data[$csv]));
                    }
                }
                update_post_meta((int) $post_id, '_ap_import_source', 'csv');
                update_post_meta((int) $post_id, '_ap_import_batch', $batch);
            }
        }
        fclose($handle);
        update_option('autoparts_last_import', ['batch' => $batch, 'created' => $created, 'updated' => $updated, 'time' => time()], false);
        wp_safe_redirect(admin_url('admin.php?page=autoparts-import&imported=1'));
        exit;
    }
}
