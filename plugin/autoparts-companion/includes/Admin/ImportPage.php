<?php

declare(strict_types=1);

namespace AutoParts\Admin;

final class ImportPage
{
    public function render(): void
    {
        include AUTOPARTS_COMPANION_PATH . 'templates/admin-import.php';
    }
}
