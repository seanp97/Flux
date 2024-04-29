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

    public static function GenerateToken($value) {
        $id = bin2hex(random_bytes(8));
        $time = microtime(true);
        $random_value = bin2hex(random_bytes(16));
        $random_number = mt_rand(0, 100);
        $combined_string = $id . $time . $random_value . $random_number . $value;
        $token = hash('sha256', $combined_string);
        return $token;
    }
}