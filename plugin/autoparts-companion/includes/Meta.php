<?php

declare(strict_types=1);

namespace AutoParts;

final class Meta
{
    public const PART_FIELDS = [
        '_ap_brand' => 'text', '_ap_model' => 'text', '_ap_generation' => 'text', '_ap_year' => 'integer', '_ap_body_type' => 'text',
        '_ap_oem' => 'text', '_ap_alt_numbers' => 'textarea', '_ap_condition' => 'text', '_ap_price' => 'number', '_ap_currency' => 'text',
        '_ap_stock_status' => 'text', '_ap_stock_qty' => 'integer', '_ap_warehouse' => 'text', '_ap_address' => 'text',
        '_ap_compatibility' => 'textarea', '_ap_internal_sku' => 'text', '_ap_external_id' => 'text', '_ap_import_source' => 'text',
        '_ap_active' => 'boolean', '_ap_seller_contact' => 'text', '_ap_delivery' => 'boolean', '_ap_pickup' => 'boolean', '_ap_warranty' => 'text',
    ];

    public const REQUEST_FIELDS = [
        '_ap_client_name' => 'text', '_ap_client_phone' => 'text', '_ap_client_messenger' => 'text', '_ap_client_email' => 'text',
        '_ap_part_id' => 'integer', '_ap_quantity' => 'integer', '_ap_comment' => 'textarea', '_ap_client_vehicle' => 'text',
        '_ap_source' => 'text', '_ap_page_url' => 'url', '_ap_utm' => 'textarea', '_ap_status' => 'text', '_ap_assigned_manager' => 'integer',
    ];

    public function register_hooks(): void
    {
        add_action('init', [$this, 'register_meta']);
    }

    public function register_meta(): void
    {
        foreach (self::PART_FIELDS as $key => $type) {
            register_post_meta('ap_part', $key, ['single' => true, 'type' => $this->rest_type($type), 'show_in_rest' => true, 'auth_callback' => '__return_true']);
        }
        foreach (self::REQUEST_FIELDS as $key => $type) {
            register_post_meta('ap_request', $key, ['single' => true, 'type' => $this->rest_type($type), 'show_in_rest' => current_user_can('edit_posts')]);
        }
    }

    public static function sanitize(string $type, mixed $value): mixed
    {
        return match ($type) {
            'integer' => absint($value),
            'number' => is_numeric($value) ? (float) $value : 0.0,
            'boolean' => (bool) $value,
            'url' => esc_url_raw((string) $value),
            'textarea' => sanitize_textarea_field((string) $value),
            default => sanitize_text_field((string) $value),
        };
    }

    private function rest_type(string $type): string
    {
        return match ($type) {'integer' => 'integer', 'number' => 'number', 'boolean' => 'boolean', default => 'string'};
    }
}
