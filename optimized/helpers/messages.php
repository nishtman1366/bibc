<?php
session_start();

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
