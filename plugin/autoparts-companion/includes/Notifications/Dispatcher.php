<?php

declare(strict_types=1);

namespace AutoParts\Notifications;

final class Dispatcher
{
    public function register_hooks(): void
    {
        add_action('autoparts_after_create_request', [$this, 'request_created'], 10, 2);
        add_action('admin_post_autoparts_test_telegram', [$this, 'test_telegram']);
    }

    public function request_created(int $request_id, array $payload): void
    {
        /** @var array<string,mixed> $settings */
        $settings = get_option('autoparts_settings', []);
        $message = $this->render_template((string) ($settings['telegram_template'] ?? $this->default_template()), $payload + ['request_id' => $request_id]);
        do_action('autoparts_before_send_notification', 'request_created', $payload);

        if (! empty($settings['email'])) {
            wp_mail(sanitize_email((string) $settings['email']), __('Новая заявка на запчасть', 'autoparts-companion'), $message);
        }
        if (! empty($settings['telegram_enabled']) && ! empty($settings['telegram_token']) && ! empty($settings['telegram_chat_id']) && function_exists('autoparts_can_use') && autoparts_can_use('telegram')) {
            $this->send_telegram((string) $settings['telegram_token'], (string) $settings['telegram_chat_id'], $message);
        }
        if (! empty($settings['webhook_url']) && function_exists('autoparts_can_use') && autoparts_can_use('webhooks')) {
            $this->send_webhook((string) $settings['webhook_url'], (string) ($settings['webhook_secret'] ?? ''), $payload + ['request_id' => $request_id]);
        }
        do_action('autoparts_after_send_notification', 'request_created', $payload);
    }

    public function test_telegram(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Недостаточно прав.', 'autoparts-companion'));
        }
        check_admin_referer('autoparts_test_telegram');
        $settings = get_option('autoparts_settings', []);
        if (! empty($settings['telegram_token']) && ! empty($settings['telegram_chat_id'])) {
            $this->send_telegram((string) $settings['telegram_token'], (string) $settings['telegram_chat_id'], __('Тестовое сообщение AutoParts: бот подключён.', 'autoparts-companion'));
        }
        wp_safe_redirect(admin_url('admin.php?page=autoparts-bot&telegram_test=1'));
        exit;
    }

    private function send_telegram(string $token, string $chat_id, string $message): void
    {
        $url = 'https://api.telegram.org/bot' . rawurlencode($token) . '/sendMessage';
        wp_remote_post($url, ['timeout' => 8, 'body' => ['chat_id' => $chat_id, 'text' => $message, 'parse_mode' => 'HTML']]);
    }

    private function send_webhook(string $url, string $secret, array $payload): void
    {
        $body = wp_json_encode($payload);
        $headers = ['Content-Type' => 'application/json'];
        if ($secret && $body) {
            $headers['X-AutoParts-Signature'] = hash_hmac('sha256', $body, $secret);
        }
        wp_remote_post($url, ['timeout' => 8, 'headers' => $headers, 'body' => $body]);
    }

    private function render_template(string $template, array $payload): string
    {
        $replacements = [];
        foreach ($payload as $key => $value) {
            $replacements['{' . $key . '}'] = is_scalar($value) ? (string) $value : wp_json_encode($value);
        }
        return strtr(apply_filters('autoparts_telegram_template', $template, $payload), $replacements);
    }

    private function default_template(): string
    {
        return "Новая заявка #{request_id}\nДеталь: {part_name}\nЦена: {price}\nКлиент: {client_name}\nТелефон: {client_phone}\nСтраница: {page_url}";
    }
}
