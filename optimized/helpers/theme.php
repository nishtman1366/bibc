<?php

if (!function_exists('assets')) {
    function assets(string $fileName)
    {
        echo sprintf("../optimized/assets/%s", $fileName);
    }
}