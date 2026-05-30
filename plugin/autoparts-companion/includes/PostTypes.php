<?php

declare(strict_types=1);

namespace AutoParts;

final class PostTypes
{
    public function register_hooks(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        register_post_type('ap_part', [
            'labels' => ['name' => __('Запчасти', 'autoparts-companion'), 'singular_name' => __('Запчасть', 'autoparts-companion')],
            'public' => true,
            'has_archive' => 'parts',
            'rewrite' => ['slug' => 'parts', 'with_front' => false],
            'menu_icon' => 'dashicons-car',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'show_in_rest' => true,
            'capability_type' => 'post',
        ]);

        register_post_type('ap_request', [
            'labels' => ['name' => __('Заявки', 'autoparts-companion'), 'singular_name' => __('Заявка', 'autoparts-companion')],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => ['title', 'editor', 'custom-fields'],
            'capability_type' => 'post',
        ]);

        register_post_type('ap_garage_vehicle', [
            'labels' => ['name' => __('Гараж', 'autoparts-companion'), 'singular_name' => __('Авто', 'autoparts-companion')],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => ['title', 'custom-fields'],
            'capability_type' => 'post',
        ]);

        $taxonomies = [
            'ap_part_category' => ['Запчасть категории', 'part-category'],
            'ap_vehicle_brand' => ['Марки авто', 'vehicle-brand'],
            'ap_vehicle_model' => ['Модели авто', 'vehicle-model'],
            'ap_part_label' => ['Метки деталей', 'part-label'],
        ];

        foreach ($taxonomies as $taxonomy => [$label, $slug]) {
            register_taxonomy($taxonomy, ['ap_part'], [
                'labels' => ['name' => __($label, 'autoparts-companion')],
                'public' => true,
                'hierarchical' => true,
                'show_admin_column' => true,
                'show_in_rest' => true,
                'rewrite' => ['slug' => $slug, 'with_front' => false],
            ]);
        }
    }
}
