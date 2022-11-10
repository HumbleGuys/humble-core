<?php

namespace HumbleCore\App;

use HumbleCore\Support\Facades\Action;

class Admin
{
    public function favicon(?string $favicon): void
    {
        if (empty($favicon)) {
            return;
        }

        Action::add('admin_head', function () use ($favicon) {
            echo "<link rel='shortcut icon' type='image/png' href='{$favicon}'>";
        });
    }
}
