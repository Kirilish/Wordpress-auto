<?php

declare(strict_types=1);

namespace AutoParts\SEO;

final class Schema
{
    public function register_hooks(): void
    {
        add_action('wp_head', [$this, 'print_schema']);
        add_filter('document_title_parts', [$this, 'title_parts']);
    }

    public function title_parts(array $parts): array
    {
        if (is_singular('ap_part')) {
            $seo = apply_filters('autoparts_seo_title', get_the_title() . ' купить — OEM ' . get_post_meta(get_the_ID(), '_ap_oem', true), get_the_ID());
            $parts['title'] = (string) $seo;
        }
        return $parts;
    }

    public function print_schema(): void
    {
        if (is_singular('ap_part')) {
            $id = get_the_ID();
            $schema = ['@context' => 'https://schema.org', '@type' => 'Product', 'name' => get_the_title($id), 'sku' => get_post_meta($id, '_ap_internal_sku', true), 'mpn' => get_post_meta($id, '_ap_oem', true), 'description' => wp_strip_all_tags(get_the_excerpt($id)), 'image' => get_the_post_thumbnail_url($id, 'large'), 'offers' => ['@type' => 'Offer', 'price' => get_post_meta($id, '_ap_price', true), 'priceCurrency' => get_post_meta($id, '_ap_currency', true) ?: 'USD', 'availability' => 'https://schema.org/InStock', 'url' => get_permalink($id)]];
            echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
        }
    }
}
