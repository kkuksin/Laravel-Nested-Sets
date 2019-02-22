<?php

namespace App\Services;


class Timer
{
    private static $beginTime;

    public static function start()
    {
        static::$beginTime = microtime(true);
    }

    public static function end()
    {
        return 'Time: '.(microtime(true) - static::$beginTime).' sec';
    }

}