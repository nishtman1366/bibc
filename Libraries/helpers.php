<?php

use Jenssegers\Blade\Blade;

if (!function_exists('assets')) {
    function assets(string $fileName)
    {
        return sprintf(BASE_PATH . "optimized/assets/%s", $fileName);
    }
}

if (!function_exists('view')) {
    function view($view, $data = [])
    {
        global $blade;
        return $blade->make($view, $data)->render();
    }
}
if (!function_exists('setMessage')) {
    function setMessage(string $message, string $type = 'success')
    {
        $list = [];
        $messages = $_SESSION['messages'];
        foreach ($messages as $item) {
            $list[] = [
                'message' => $item['message'],
                'type' => $item['type']
            ];
        }
        $list[] = [
            'message' => $message,
            'type' => $type
        ];
        $_SESSION['messages'] = $list;
    }
}
if (!function_exists('getMessages')) {
    function getMessages()
    {
        $list = [];
        $messages = $_SESSION['messages'];
        foreach ($messages as $item) {
            $list[] = [
                'message' => $item['message'],
                'type' => $item['type']
            ];
        }
        $_SESSION['messages'] = [];
        return $list;
    }
}
if (!function_exists('encrypt')) {
    function encrypt($data)
    {
        for ($i = 0, $key = 27, $c = 48; $i <= 255; $i++) {
            $c = 255 & ($key ^ ($c << 1));
            $table[$key] = $c;
            $key = 255 & ($key + 1);
        }
        $len = strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $data[$i] = chr($table[ord($data[$i])]);
        }
        return base64_encode($data);
    }
}
if (!function_exists('decrypt')) {
    function decrypt($data)
    {
        $data = base64_decode($data);
        for ($i = 0, $key = 27, $c = 48; $i <= 255; $i++) {
            $c = 255 & ($key ^ ($c << 1));
            $table[$c] = $key;
            $key = 255 & ($key + 1);
        }
        $len = strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $data[$i] = chr($table[ord($data[$i])]);
        }
        return $data;
    }
}