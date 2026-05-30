<div class="wrap ap-admin">
    <h1>AutoParts Dashboard</h1>
    <div class="ap-admin-grid">
        <div class="ap-admin-card"><span>Запчастей</span><strong><?php echo esc_html((string) (($parts->publish ?? 0) + ($parts->draft ?? 0))); ?></strong></div>
        <div class="ap-admin-card"><span>Новых заявок</span><strong><?php echo esc_html((string) ($requests->publish ?? 0)); ?></strong></div>
        <div class="ap-admin-card"><span>Бот</span><strong><?php echo ! empty(get_option('autoparts_settings', [])['telegram_enabled']) ? 'Telegram ON' : 'Email базовый'; ?></strong></div>
        <div class="ap-admin-card"><span>Лицензия</span><strong><?php echo esc_html(ucfirst(\AutoParts\Premium\FeatureFlags::plan())); ?></strong></div>
    </div>
    <div class="ap-admin-panel"><h2>Коммерческая архитектура</h2><p>Данные магазина находятся в companion plugin: CPT, заявки, импорт, уведомления, REST API и feature flags. Тема отвечает за визуальный слой.</p></div>
</div>
