<?php

namespace Models;

class Log {
    public static function error($message) {
        echo $message;
    }
    public static function critical($message) {
        echo $message;
        die();
    }
    public static function notice($message) {
        echo $message;
    }
}