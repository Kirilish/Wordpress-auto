<?php

declare(strict_types=1);

namespace AutoParts\API;

use AutoParts\Meta;

final class RestController
{
    public function register_hooks(): void
    {
        add_action('rest_api_init', [$this, 'routes']);
    }

    public function routes(): void
    {
        register_rest_route('autoparts/v1', '/parts', ['methods' => 'GET', 'callback' => [$this, 'parts'], 'permission_callback' => '__return_true']);
        register_rest_route('autoparts/v1', '/parts/(?P<id>\d+)', ['methods' => 'GET', 'callback' => [$this, 'part'], 'permission_callback' => '__return_true']);
        register_rest_route('autoparts/v1', '/requests', ['methods' => 'POST', 'callback' => [$this, 'create_request'], 'permission_callback' => [$this, 'public_nonce']]);
        register_rest_route('autoparts/v1', '/brands', ['methods' => 'GET', 'callback' => fn() => $this->terms('ap_vehicle_brand'), 'permission_callback' => '__return_true']);
        register_rest_route('autoparts/v1', '/models', ['methods' => 'GET', 'callback' => fn() => $this->terms('ap_vehicle_model'), 'permission_callback' => '__return_true']);
        register_rest_route('autoparts/v1', '/categories', ['methods' => 'GET', 'callback' => fn() => $this->terms('ap_part_category'), 'permission_callback' => '__return_true']);
        register_rest_route('autoparts/v1', '/compatibility', ['methods' => 'POST', 'callback' => [$this, 'compatibility'], 'permission_callback' => '__return_true']);
        register_rest_route('autoparts/v1', '/favorites', ['methods' => 'GET', 'callback' => fn() => ['items' => []], 'permission_callback' => '__return_true']);
        register_rest_route('autoparts/v1', '/garage', ['methods' => 'GET', 'callback' => fn() => ['items' => []], 'permission_callback' => '__return_true']);
        register_rest_route('autoparts/v1', '/notifications/test', ['methods' => 'POST', 'callback' => fn() => ['ok' => true], 'permission_callback' => fn() => current_user_can('manage_options')]);
    }

    public function public_nonce(\WP_REST_Request $request): bool
    {
        $nonce = (string) $request->get_header('X-WP-Nonce');
        return (bool) wp_verify_nonce($nonce, 'wp_rest');
    }

    public function parts(\WP_REST_Request $request): \WP_REST_Response
    {
        $args = ['post_type' => 'ap_part', 'post_status' => 'publish', 'posts_per_page' => min(48, max(1, (int) $request->get_param('per_page') ?: 12)), 'paged' => max(1, (int) $request->get_param('page'))];
        $meta_query = ['relation' => 'AND'];
        foreach (['_ap_brand' => 'brand', '_ap_model' => 'model', '_ap_oem' => 'oem', '_ap_condition' => 'condition', '_ap_stock_status' => 'stock'] as $meta => $param) {
            $value = sanitize_text_field((string) $request->get_param($param));
            if ($value) {
                $meta_query[] = ['key' => $meta, 'value' => $value, 'compare' => 'LIKE'];
            }
        }
        $min = $request->get_param('price_min');
        $max = $request->get_param('price_max');
        if (is_numeric($min) || is_numeric($max)) {
            $meta_query[] = ['key' => '_ap_price', 'value' => [(float) $min, (float) ($max ?: PHP_INT_MAX)], 'type' => 'NUMERIC', 'compare' => 'BETWEEN'];
        }
        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }
        $search = sanitize_text_field((string) $request->get_param('search'));
        if ($search) {
            $args['s'] = $search;
        }
        $sort = sanitize_key((string) $request->get_param('sort'));
        if ('price_asc' === $sort || 'price_desc' === $sort) {
            $args['meta_key'] = '_ap_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'price_asc' === $sort ? 'ASC' : 'DESC';
        }
        $query = new \WP_Query(apply_filters('autoparts_catalog_filters', $args, $request));
        $items = array_map([$this, 'format_part'], $query->posts);
        return rest_ensure_response(['items' => $items, 'total' => (int) $query->found_posts, 'pages' => (int) $query->max_num_pages]);
    }

    public function part(\WP_REST_Request $request): \WP_REST_Response
    {
        $post = get_post((int) $request['id']);
        if (! $post || 'ap_part' !== $post->post_type) {
            return new \WP_REST_Response(['message' => 'Not found'], 404);
        }
        return rest_ensure_response($this->format_part($post));
    }

    public function create_request(\WP_REST_Request $request): \WP_REST_Response
    {
        $payload = [
            'client_name' => sanitize_text_field((string) $request->get_param('client_name')),
            'client_phone' => sanitize_text_field((string) $request->get_param('client_phone')),
            'client_email' => sanitize_email((string) $request->get_param('client_email')),
            'client_messenger' => sanitize_text_field((string) $request->get_param('client_messenger')),
            'part_id' => absint($request->get_param('part_id')),
            'quantity' => max(1, absint($request->get_param('quantity'))),
            'comment' => sanitize_textarea_field((string) $request->get_param('comment')),
            'client_vehicle' => sanitize_text_field((string) $request->get_param('client_vehicle')),
            'source' => sanitize_text_field((string) $request->get_param('source')),
            'page_url' => esc_url_raw((string) $request->get_param('page_url')),
            'utm_source' => sanitize_text_field((string) $request->get_param('utm_source')),
            'date' => current_time('mysql'),
        ];
        do_action('autoparts_before_create_request', $payload);
        $part_title = $payload['part_id'] ? get_the_title($payload['part_id']) : __('Подбор запчасти', 'autoparts-companion');
        $request_id = wp_insert_post(['post_type' => 'ap_request', 'post_status' => 'publish', 'post_title' => sprintf('Заявка: %s — %s', $payload['client_phone'], $part_title), 'post_content' => $payload['comment']], true);
        if (is_wp_error($request_id)) {
            return new \WP_REST_Response(['message' => $request_id->get_error_message()], 500);
        }
        foreach (Meta::REQUEST_FIELDS as $meta => $type) {
            $key = substr($meta, 4);
            if (isset($payload[$key])) {
                update_post_meta((int) $request_id, $meta, Meta::sanitize($type, $payload[$key]));
            }
        }
        update_post_meta((int) $request_id, '_ap_status', 'new');
        $payload['part_name'] = $part_title;
        $payload['price'] = $payload['part_id'] ? get_post_meta($payload['part_id'], '_ap_price', true) : '';
        do_action('autoparts_after_create_request', (int) $request_id, $payload);
        return rest_ensure_response(['id' => (int) $request_id, 'status' => 'new']);
    }

    public function terms(string $taxonomy): array
    {
        return array_map(static fn($term) => ['id' => $term->term_id, 'name' => $term->name, 'slug' => $term->slug], get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]));
    }

    public function compatibility(\WP_REST_Request $request): array
    {
        $part_id = absint($request->get_param('part_id'));
        $vehicle = sanitize_text_field((string) $request->get_param('vehicle'));
        $compat = (string) get_post_meta($part_id, '_ap_compatibility', true);
        $result = $vehicle && $compat && false !== stripos($compat, $vehicle);
        return ['compatible' => apply_filters('autoparts_compatibility_result', $result, $part_id, $vehicle)];
    }

    private function format_part(\WP_Post $post): array
    {
        $price = apply_filters('autoparts_part_price', get_post_meta($post->ID, '_ap_price', true), $post->ID);
        return ['id' => $post->ID, 'title' => get_the_title($post), 'url' => get_permalink($post), 'image' => get_the_post_thumbnail_url($post, 'medium_large'), 'excerpt' => get_the_excerpt($post), 'brand' => get_post_meta($post->ID, '_ap_brand', true), 'model' => get_post_meta($post->ID, '_ap_model', true), 'generation' => get_post_meta($post->ID, '_ap_generation', true), 'oem' => get_post_meta($post->ID, '_ap_oem', true), 'price' => $price, 'currency' => get_post_meta($post->ID, '_ap_currency', true) ?: 'USD', 'condition' => get_post_meta($post->ID, '_ap_condition', true), 'stock' => get_post_meta($post->ID, '_ap_stock_status', true), 'warehouse' => get_post_meta($post->ID, '_ap_warehouse', true)];
    }
}
