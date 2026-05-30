<?php

declare(strict_types=1);

namespace AutoParts\Premium;

final class FeatureFlags
{
    private const MATRIX = [
        'free' => ['csv_import', 'email_notifications', 'basic_catalog', 'basic_requests', 'design_basic'],
        'premium' => ['csv_import', 'advanced_import', 'telegram', 'messengers', 'webhooks', 'garage', 'garage_multi', 'favorites_compare', 'advanced_filters', 'schema', 'analytics', 'design_builder'],
        'pro' => ['csv_import', 'advanced_import', 'telegram', 'messengers', 'webhooks', 'garage', 'garage_multi', 'favorites_compare', 'advanced_filters', 'schema', 'analytics', 'design_builder', 'managers', 'multiwarehouse', 'multicurrency', 'supplier_api', 'ai_assistant', 'white_label'],
    ];

    public static function plan(): string
    {
        $plan = (string) get_option('autoparts_license_plan', 'free');
        return in_array($plan, ['free', 'premium', 'pro'], true) ? $plan : 'free';
    }

    public static function can_use(string $feature): bool
    {
        $plan = self::plan();
        if ('expired' === get_option('autoparts_license_status', 'active')) {
            return in_array($feature, self::MATRIX['free'], true);
        }
        return in_array($feature, self::MATRIX[$plan], true) || ('pro' === $plan && in_array($feature, self::MATRIX['premium'], true));
    }

    public static function limit(string $resource): int
    {
        if ('parts' === $resource && 'free' === self::plan()) {
            return 100;
        }
        return -1;
    }
}
