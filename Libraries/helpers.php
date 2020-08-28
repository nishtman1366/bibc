<?php

use Jenssegers\Blade\Blade;

if (!function_exists('assets')) {
    function assets(string $fileName)
    {
        return sprintf(BASE_PATH . "resources/assets/%s", $fileName);
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

if (!function_exists('tripCurrency')) {
    function tripCurrency($price = null, $ratio = null, $defCurrency = null, $decimals = 0)
    {
        if (is_null($defCurrency)) {
            $currency = \App\Models\Currency::where('eDefault', 'Yes')->get()->first();
        } else {
            $currency = \App\Models\Currency::where('vName', $defCurrency)->get()->first();
        }

        if (is_null($ratio) || $ratio == 0) {
            $formattedPrice = number_format($price, $decimals);
        } else {
            $formattedPrice = number_format($price * $ratio, $decimals);
        }
        if (is_null($currency)) {
            return $formattedPrice;
        } else {
            return sprintf('%s %s', $formattedPrice, $currency->vSymbol);
        }
    }
}

if (!function_exists('addCurrencySymbol')) {
    function addCurrencySymbol($price = null, $ratio = null, $defCurrency = null, $decimals = 0)
    {
        if (is_null($defCurrency)) {
            $currency = \App\Models\Currency::where('eDefault', 'Yes')->get()->first();
        } else {
            $currency = \App\Models\Currency::where('vName', $defCurrency)->get()->first();
        }

        if (is_null($ratio) || $ratio == 0) {
            $formattedPrice = number_format($price, $decimals);
        } else {
            $formattedPrice = number_format($price * $ratio, $decimals);
        }
        if (is_null($currency)) {
            return $formattedPrice;
        } else {
            return sprintf('%s %s', $formattedPrice, $currency->vSymbol);
        }
    }
}