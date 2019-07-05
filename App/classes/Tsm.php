<?php

namespace App\Classes;

class Tsm
{
    public static function sendMessage($msg)
    {
        $token = 'STUB';
        $chat_id = 'STUB';
        
        $text = $msg;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . $token . '/sendMessage');
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 20000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'chat_id=' . $chat_id . '&text=' . urlencode($text) . '&parse_mode=markdown');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        // Настройки прокси, если это необходимо
        $proxy = 'STUB';
        // $auth = 'login:password';
        $auth = '';
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        // curl_setopt($ch, CURLOPT_PROXYUSERPWD, $auth);
        
        // Отправить сообщение
        $result = curl_exec($ch);
        $errCurlNo = curl_errno($ch);
        $errCurl = curl_error($ch);
        curl_close($ch);
    
        if($errCurlNo > 0) {
            Logg::info($errCurl, false);
        }
        
        Logg::info($result, false);
    }
    
}