<?php

function flux_autoload() {
    
    $folders = array("core", "controllers", "models", "config", "");

    foreach ($folders as $folder) {
        $directory = __DIR__ . '/' . $folder . '/';

        foreach (glob($directory . '*.php') as $file) {
            require_once $file;
        }
    }
}

flux_autoload();