<?php

declare(strict_types=1);

namespace AutoParts;

use AutoParts\Admin\Admin;
use AutoParts\API\RestController;
use AutoParts\Frontend\Shortcodes;
use AutoParts\Import\CsvImporter;
use AutoParts\Notifications\Dispatcher;
use AutoParts\Premium\LicenseManager;
use AutoParts\SEO\Schema;
use AutoParts\Setup\Wizard;

final class Core
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function boot(): void
    {
        load_plugin_textdomain('autoparts-companion', false, dirname(plugin_basename(AUTOPARTS_COMPANION_FILE)) . '/languages');

        (new PostTypes())->register_hooks();
        (new Meta())->register_hooks();
        (new Admin())->register_hooks();
        (new RestController())->register_hooks();
        (new Shortcodes())->register_hooks();
        (new CsvImporter())->register_hooks();
        (new Dispatcher())->register_hooks();
        (new LicenseManager())->register_hooks();
        (new Schema())->register_hooks();
        (new Wizard())->register_hooks();
    }
}
