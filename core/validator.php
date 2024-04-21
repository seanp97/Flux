<?php

class Validator {

    public static function Email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function String($str, $min = 1, $max = 5000) {
        $str = trim($str);
        return strlen($str) >= $min && strlen($str) <= $max;
    }

    public static function Integer($number) {
        return filter_var($number, FILTER_VALIDATE_INT) !== false;
    }

    public static function Numeric($number) {
        return is_numeric($number);
    }

    public static function URL($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function Date($date, $format = 'd-m-Y') {
        $parsedDate = date_parse_from_format($format, $date);
        return $parsedDate['error_count'] === 0 && $parsedDate['warning_count'] === 0;
    }

    public static function StringCompare($str1, $str2) {
        return strcmp($str1, $str2) === 0;
    }

}