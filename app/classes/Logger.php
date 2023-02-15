<?php

namespace OJSXml;

use DateTime;
use DateTimeZone;

class Logger
{
    public static array $messages = [];

    private static string $fileName = '';

    /**
     * @throws \Exception
     */
    public static function __constructStatic()
    {
        $tz = 'America/Vancouver';
        $timestamp = time();
        $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
        self::$fileName = $dt->format('YmdHi') . '_log.txt';
    }

    public static function print(string $message)
    {
        array_push(self::$messages, $message);
        echo $message . PHP_EOL;
    }


    public static function writeOut($command, $user)
    {
        $logPath = Config::get('logLocation');
        if (!file_exists($logPath)) {
            $logPath = dirname(__FILE__, 3);
            Logger::print("Could not resolve 'logLocation': No such file or directory. Using $logPath instead.");
        }
        $file = fopen($logPath . '/' . $command . '_' . $user . '_' . self::$fileName, 'w');
        if ($file !== false) {
            fwrite($file, self::formatToString(self::$messages));
            fclose($file);
        } else {
            echo 'Cannot write log to file' . PHP_EOL;
        }
    }

    /**
     * @param $string
     * @return string
     */
    private static function formatToString(array $messages): string
    {
        $returner = '';

        foreach ($messages as $message) {
            $returner .= $message . PHP_EOL;
        }

        return $returner;
    }
}

Logger::__constructStatic();
