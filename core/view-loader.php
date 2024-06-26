<?php

function view($file, $data = []) {
    extract($data);

    if(str_contains($file, 'views')) {
        if(str_contains($file, '.php')) {
            require $file;
        }
        else {
            require $file . ".php";
        }
    } else {
        if(str_contains($file, '.php')) {
            require 'views/' . $file;
        } else {
            require 'views/' . $file . ".php";
        }
    }
}
