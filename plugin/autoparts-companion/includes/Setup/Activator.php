<?php

declare(strict_types=1);

namespace AutoParts\Setup;

use AutoParts\PostTypes;

final class Activator
{
    public static function activate(): void
    {
        (new PostTypes())->register();
        self::create_pages();
        add_option('autoparts_license_plan', 'free', '', false);
        add_option('autoparts_license_status', 'active', '', false);
        add_option('autoparts_wizard_pending', 1, '', false);
        flush_rewrite_rules();
    }

    public static function create_pages(): void
    {
        $pages = ['Главная' => '[autoparts_search][autoparts_catalog]', 'Каталог' => '[autoparts_catalog]', 'Контакты' => '[autoparts_cta]', 'Доставка' => 'Условия доставки и самовывоза.', 'Оплата' => 'Оплата наличными, картой или по счёту.', 'Гарантия' => 'Гарантия на проверенные детали.', 'Корзина/Заявка' => '[autoparts_request_form part_id="0"]', 'Избранное' => '[autoparts_favorites]', 'Гараж' => '[autoparts_garage]', 'Политика конфиденциальности' => 'Политика обработки персональных данных.'];
        foreach ($pages as $title => $content) {
            if (! get_page_by_title($title)) {
                wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_content' => $content]);
            }
        }
    }
}
