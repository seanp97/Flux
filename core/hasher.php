<?php

class Hasher {
    private static $options = [
        "salt" => "abcdef12345",
    ];

    public static function SHA256($str) {
        return hash('sha256', $str . Hasher::$options['salt']);
    }

    public static function SHA1($str) {
        return sha1($str . Hasher::$options['salt']);
    }

    public static function MD5($str) {
        return md5($str . Hasher::$options['salt']);
    }
}