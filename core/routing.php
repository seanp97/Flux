<?php

class Http {

    private static function HandleRequest($path, $request, $cb) {
        try {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                if ($_SERVER['REQUEST_METHOD'] === "$request") {

                    $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
                    $uri = Explode('/', $uri);
                    $uri = $uri[count($uri) - 1];
                    $uri = '/' . $uri;

                    $queryString = $_SERVER['QUERY_STRING'];

                    parse_str($queryString, $params);

                    $paramVal = reset($params);

                    if($path == $uri) {
                        if($cb) {
                            if($queryString) {
                                $cb($paramVal);
                            }
                            else {
                                $cb();
                            }
                            
                        }
                    }
                }
            }
        }
        catch(Exception $e) {
            echo $e;
        }
    }

    private static function ExtractQueryStringValue($queryString) {
        parse_str($queryString, $params);
    
        if (!empty($params)) {
            return reset($params);
        } else {
            return null;
        }
    }

    static function path($path, $file) {
        try {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
                $uri = Explode('/', $uri);
                $uri = $uri[count($uri) - 1];
                $uri = '/' . $uri;
    
                if(str_contains($file, 'views')) {
                    if($uri == $path) {
                        require $file;
                    }
                }
                else {
                    if($uri == $path) {
                        require 'views/' . $file;
                    }
                }
            }
        }
        catch(Exception $e) {
            echo $e;
        }
    }

    static function get($path, $cb = false) {
        self::HandleRequest($path, 'GET', $cb);
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