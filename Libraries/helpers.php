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