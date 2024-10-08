<?php

class Http {

    private static function HandleRequest($path, $request, $cb) {
        try {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                if ($_SERVER['REQUEST_METHOD'] === "$request") {
                    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                    $pattern = preg_replace('/{(\w+)}/', '([^/]+)', $path);
                    $pattern = '#^' . $pattern . '$#';

                    if (preg_match($pattern, $uri, $matches)) {
                        if (isset($matches[1])) {
                            $id = $matches[1];
                            call_user_func($cb, $id);
                        } else {
                            call_user_func($cb);
                        }
                    }
                }
            }
        } catch(Exception $e) {
            echo $e;
        }
    }

    static function path($path, $file, $data = []) {
        try {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
                $uri = explode('/', $uri);
                $uri = '/' . end($uri);
    
                if (str_contains($file, 'views')) {
                    if ($uri == $path) {
                        extract($data);
                        require $file;
                    }
                } else {
                    if ($uri == $path) {
                        extract($data);
                        require 'views/' . $file;
                    }
                }
            }
        } catch (Exception $e) {
            echo $e;
        }
    }
    
    static function get($path, $cb = false, $data = []) {
        self::HandleRequest($path, 'GET', $cb, $data);
    }    

    static function post($path, $cb = false) {
        self::HandleRequest($path, 'POST', $cb);
    }

    static function put($path, $cb = false) {
        self::HandleRequest($path, 'PUT', $cb);
    }

    static function delete($path, $cb = false) {
        self::HandleRequest($path, 'DELETE', $cb);
    }

    static function patch($path, $cb = false) {
        self::HandleRequest($path, 'PATCH', $cb);
    }
}