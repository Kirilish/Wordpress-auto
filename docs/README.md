# AutoParts Premium — документация

## Установка

1. Скопируйте `plugin/autoparts-companion` в `wp-content/plugins/autoparts-companion` и активируйте plugin.
2. Скопируйте `theme/autoparts-premium` в `wp-content/themes/autoparts-premium` и активируйте тему.
3. После активации plugin откроется мастер запуска. Он создаёт основные страницы: Главная, Каталог, Контакты, Доставка, Оплата, Гарантия, Корзина/Заявка, Избранное, Гараж, Политика конфиденциальности.

## Архитектура

- Theme отвечает за внешний вид, шаблоны, адаптивность, Customizer и анимации.
- Companion Plugin отвечает за данные и бизнес-логику: CPT `ap_part`, `ap_request`, `ap_garage_vehicle`, импорт, REST API, уведомления, лицензии и SEO schema.

## Добавление запчастей

- Полноценная форма: `Запчасти → Добавить` с SaaS-like метабоксом полей.
- Быстрое добавление: `AutoParts → Быстро добавить`.
- CSV импорт: `AutoParts → Импорт`. Базовый CSV доступен в Free, расширенные источники заложены через feature flags Premium/Pro.

## CSV формат

Поддерживаемые колонки: `title, brand, model, generation, oem, price, currency, condition, stock, warehouse, external_id, description`.

`external_id` используется для дедупликации: существующая деталь обновляется, новая создаётся.

## Уведомления

`AutoParts → Бот`:

- Email получателя.
- Telegram enable/token/chat_id/template.
- WhatsApp/Viber phone для кнопок и ссылок.
- Webhook URL + HMAC secret.

Шаблон Telegram поддерживает переменные: `{part_name}`, `{price}`, `{client_name}`, `{client_phone}`, `{page_url}`, `{utm_source}`, `{date}` и другие поля payload.

## REST API

Namespace: `/wp-json/autoparts/v1`.

- `GET /parts`
- `GET /parts/{id}`
- `POST /requests` с `X-WP-Nonce`
- `GET /brands`, `/models`, `/categories`
- `POST /compatibility`
- `GET /favorites`, `/garage`

## Shortcodes

- `[autoparts_catalog]`
- `[autoparts_search]`
- `[autoparts_request_form]`
- `[autoparts_garage]`
- `[autoparts_favorites]`
- `[autoparts_cta]`

## Premium hooks

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
