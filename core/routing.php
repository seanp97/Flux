<?php

class Http {

    static function path($path, $file) {
        try {
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
        catch(Exception $e) {
            echo $e;
        }
    }

    static function get($path, $cb = false) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {

                $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
                $uri = Explode('/', $uri);
                $uri = $uri[count($uri) - 1];
                $uri = '/' . $uri;

                if($path == $uri) {
                    if($cb) {
                        $cb();
                    }
                }
            }
        }
        catch(Exception $e) {
            echo $e;
        }
    }

    static function post($path, $cb = false) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
                $uri = Explode('/', $uri);
                $uri = $uri[count($uri) - 1];
                $uri = '/' . $uri;

                if($path == $uri) {
                    if($cb) {
                        $cb();
                    }
                }
            }
        }
        catch(Exception $e) {
            echo $e;
        }
    }

    static function put($path, $cb = false) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

                $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
                $uri = Explode('/', $uri);
                $uri = $uri[count($uri) - 1];
                $uri = '/' . $uri;

                if($path == $uri) {
                    if($cb) {
                        $cb();
                    }
                }
            }
        }
        catch(Exception $e) {
            echo $e;
        }
    }

    static function delete($path, $cb = false) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

                $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
                $uri = Explode('/', $uri);
                $uri = $uri[count($uri) - 1];
                $uri = '/' . $uri;

                if($path == $uri) {
                    if($cb) {
                        $cb();
                    }
                }
            }
        }
        catch(Exception $e) {
            echo $e;
        }
    }

    static function patch($path, $cb = false) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {

                $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
                $uri = Explode('/', $uri);
                $uri = $uri[count($uri) - 1];
                $uri = '/' . $uri;

                if($path == $uri) {
                    if($cb) {
                        $cb();
                    }
                }
            }
        }
        catch(Exception $e) {
            echo $e;
        }
    }
}