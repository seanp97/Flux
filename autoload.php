<?php

function twister_autoload() {
    
    $folders = array("core", "controllers", "models", "config", "");

    foreach ($folders as $folder) {
        $directory = __DIR__ . '/' . $folder . '/';

        foreach (glob($directory . '*.php') as $file) {
            require_once $file;
        }
    }
}

twister_autoload();