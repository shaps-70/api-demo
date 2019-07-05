<?php

namespace App\Classes;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class Logg
{
    private static $foreground_colors = array(
        'bold' => '1',
        'dim' => '2',
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'INFO' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'ERROR' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'WARNING' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37',
        'normal' => '0;39',
    );
    private static $background_colors = array(
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47',
    );
    
    public static function info($msg, $toConsole = false)
    {
        self::pushLogToMessage('INFO', $msg, $toConsole);
    }
    
    public static function warn($msg, $toConsole = false)
    {
        self::pushLogToMessage('WARNING', $msg, $toConsole);
    }
    
    public static function error($msg, $method = null, $toConsole = false)
    {
        self::pushLogToMessage('ERROR', ($method ? ('(' . $method . '): ') : '') . $msg, $toConsole);
    }
    
    private static function getLogger($level)
    {
        
        $dateFormat = "ymd H:i:s";
        switch ($level) {
            case 'INFO':
                $output = "[%datetime%] %level_name%:%message%\n";
                break;
            default:
                $output = "[%datetime%] %level_name%:\n%message%\n\n";
        }
        $formatter = new LineFormatter($output, $dateFormat);
        
        $stream = new StreamHandler(__DIR__ . '/../../logs/app.log', $level);
        $stream->setFormatter($formatter);
        
        $loggr = new Logger('cldApi');
        $loggr->pushHandler($stream);
        
        return $loggr;
        
    }
    
    private static function pushLogToMessage($level, $message, $toConsole)
    {
        $message = print_r($message, true);
        switch ($level) {
            case 'ERROR':
                self::getLogger($level)->error($message);
                break;
            case 'WARNING':
                self::getLogger($level)->warning($message);
                break;
            case 'INFO':
                self::getLogger($level)->info($message);
                break;
        }
        if ($toConsole) {
            echo PHP_EOL . "\033[" . self::$foreground_colors[$level] . "m" . $message . "\033[0m" . PHP_EOL;
            ob_flush();
        }
    }
    
}