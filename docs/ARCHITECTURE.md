# AutoParts Premium — архитектура коммерческого WordPress-продукта

## Продуктовая модель

AutoParts Premium разделён на два независимых пакета:

1. **Theme `autoparts-premium`** — отвечает только за presentation layer: шаблоны, сетки, карточки, адаптивный UI, анимации, Customizer и визуальные настройки.
2. **Companion Plugin `autoparts-companion`** — владеет бизнес-логикой: Custom Post Types, мета-поля, заявки, импорт, REST/AJAX API, уведомления, wizard, premium feature flags, demo import, SEO schema и интеграции.

Так данные магазина не зависят от темы: при смене визуального слоя остаются запчасти, заявки, гараж, избранное, настройки, логи импорта и лицензия.

## Слои companion plugin

- `Core` — bootstrap, автозагрузка классов, регистрация сервисов.
- `PostTypes` — CPT `ap_part`, `ap_request`, `ap_garage_vehicle` и таксономии.
- `Meta` — декларативная схема мета-полей запчастей и заявок.
- `Admin` — SaaS-like dashboard, настройки, таблицы, wizard, quick add, import UI.
- `Frontend` — shortcode-блоки каталога, поиска, заявки, гаража, избранного и CTA.
- `API` — REST endpoints `/autoparts/v1/*`, nonce/API-key protection для write endpoints.
- `Import` — CSV parser/importer с preview, mapping, duplicate strategy, logs и rollback marker.
- `Notifications` — email, Telegram, webhook, WhatsApp/Viber links; единый dispatcher событий.
- `Premium` — license manager и feature flags Free/Premium/Pro с graceful degradation.
- `SEO` — schema.org Product/LocalBusiness/FAQ, OpenGraph, canonical helpers.
- `Setup` — activation, page creator, wizard state, rewrite flush.

## Theme structure

- `functions.php` — минимальный bootstrap темы.
- `inc/Theme.php` — setup, scripts, menus, supports.
- `inc/Customizer.php` — настройки дизайна без кода.
- `front-page.php`, `archive-ap_part.php`, `single-ap_part.php`, `404.php`, `search.php` — основные шаблоны.
- `template-parts/part-card.php` — единая карточка детали.
- `assets/css/theme.css` — премиальный графитовый/light responsive UI.
- `assets/js/theme.js` — лёгкие взаимодействия без тяжёлых зависимостей.

## Custom Post Types и таксономии

### CPT `ap_part` — Запчасть

Публичный CPT с archive `/parts/` и single `/parts/{slug}/`. Ключевые мета-поля:

- `_ap_brand`, `_ap_model`, `_ap_generation`, `_ap_year`, `_ap_body_type`
- `_ap_oem`, `_ap_alt_numbers`, `_ap_condition`, `_ap_price`, `_ap_currency`
- `_ap_stock_status`, `_ap_stock_qty`, `_ap_warehouse`, `_ap_address`
- `_ap_compatibility`, `_ap_internal_sku`, `_ap_external_id`, `_ap_import_source`
- `_ap_active`, `_ap_seller_contact`, `_ap_delivery`, `_ap_pickup`, `_ap_warranty`

Таксономии:

- `ap_part_category` — категория детали.
- `ap_vehicle_brand` — бренд авто.
- `ap_vehicle_model` — модель авто.
- `ap_part_label` — оригинал, аналог, контрактная, срочно, хит, скидка.

### CPT `ap_request` — Заявка

Непубличный CPT для CRM-воронки. Мета-поля:

- `_ap_client_name`, `_ap_client_phone`, `_ap_client_messenger`, `_ap_client_email`
- `_ap_part_id`, `_ap_quantity`, `_ap_comment`, `_ap_client_vehicle`
- `_ap_source`, `_ap_page_url`, `_ap_utm`, `_ap_status`, `_ap_assigned_manager`

### CPT `ap_garage_vehicle` — Авто в гараже

Непубличный CPT для зарегистрированных пользователей и менеджеров: марка, модель, поколение, год, VIN, двигатель, кузов, owner user ID.

## Premium/free feature flags

Feature flags централизованы в `Premium\FeatureFlags` и проверяются через `autoparts_can_use( $feature )`.

| Feature | Free | Premium | Pro |
| --- | --- | --- | --- |
| `parts_limit` | 100 | unlimited | unlimited |
| `csv_import` | yes | yes | yes |
| `advanced_import` | no | XML/JSON/API/Sheets | suppliers + dedupe |
| `telegram` | no | yes | yes |
| `webhooks` | no | yes | yes |
| `garage_multi` | one vehicle | multi | multi + manager tools |
| `favorites_compare` | no | yes | yes |
| `advanced_filters` | limited | yes | yes |
| `analytics` | basic | advanced | reports/export |
| `managers` | no | no | roles/history |
| `ai_assistant` | no | optional | optional + manager replies |
| `white_label` | no | no | yes |

Если лицензия истекла, данные не удаляются. Премиум UI показывает аккуратный upsell, write-действия отключаются, read-only данные остаются доступными.

## REST API

Namespace: `/wp-json/autoparts/v1`.

- `GET /parts` — каталог с фильтрами и пагинацией.
- `GET /parts/{id}` — одна запчасть.
- `POST /requests` — создать заявку, nonce protected.
- `GET /brands`, `GET /models`, `GET /categories` — справочники.
- `POST /compatibility` — проверить совместимость.
- `GET /favorites`, `GET /garage` — пользовательские данные.
- `POST /notifications/test` — тест уведомлений, capability protected.

## Security baseline

- Все входные данные проходят `sanitize_*`, `absint`, allowlist или `wp_kses_post`.
- Все output-шаблоны используют `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`.
- AJAX/REST write endpoints требуют nonce/capabilities.
- Настройки бота хранятся в options; токен маскируется в UI.
- Webhook payload подписывается HMAC секретом.
- SQL выполняется через WP_Query/meta_query или `$wpdb->prepare`.

## Hooks для расширения

- `autoparts_before_create_request`
- `autoparts_after_create_request`
- `autoparts_before_send_notification`
- `autoparts_after_send_notification`
- `autoparts_part_card_fields`
- `autoparts_catalog_filters`
- `autoparts_seo_title`
- `autoparts_telegram_template`
- `autoparts_part_price`
- `autoparts_compatibility_result`
