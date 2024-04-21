<?php

class Hasher {
    private static $options = [
        "salt" => "abcdef12345",
    ];

    public static function Hash($str) {
        return hash('sha256', $str . Hasher::$options['salt']);
    }
}