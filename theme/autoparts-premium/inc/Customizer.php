<?php

declare(strict_types=1);

namespace AutoPartsPremium;

final class Customizer
{
    public static function register(\WP_Customize_Manager $wp_customize): void
    {
        $wp_customize->add_section('ap_design', ['title' => __('AutoParts дизайн', 'autoparts-premium'), 'priority' => 35]);
        $settings = [
            'ap_primary_color' => ['Основной цвет', '#f59e0b', 'color'],
            'ap_accent_color' => ['Дополнительный цвет', '#2563eb', 'color'],
            'ap_radius' => ['Радиус карточек', 22, 'number'],
            'ap_dark_mode' => ['Тёмная тема', false, 'checkbox'],
            'ap_reduce_motion' => ['Уменьшить анимации', false, 'checkbox'],
            'ap_sticky_header' => ['Липкая шапка', true, 'checkbox'],
            'ap_mobile_bottom_nav' => ['Нижнее мобильное меню', true, 'checkbox'],
        ];
        foreach ($settings as $id => [$label, $default, $type]) {
            $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => self::sanitize($type)]);
            $control_class = 'color' === $type ? \WP_Customize_Color_Control::class : \WP_Customize_Control::class;
            $wp_customize->add_control(new $control_class($wp_customize, $id, ['label' => __($label, 'autoparts-premium'), 'section' => 'ap_design', 'settings' => $id, 'type' => $type]));
        }
    }

    private static function sanitize(string $type): callable
    {
        return match ($type) {
            'color' => 'sanitize_hex_color',
            'number' => 'absint',
            'checkbox' => static fn($v): bool => (bool) $v,
            default => 'sanitize_text_field',
        };
    }
}
