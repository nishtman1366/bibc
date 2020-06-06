<?php
if (!function_exists('adminUrl')) {
    function adminUrl(string $module = null, array $params = [], bool $clean = false)
    {
//        require_once 'Url.php';
//        return (new Url())->adminUrl($module, $params, $clean);
        if ($clean === false) {
            $queryString = null;
            if (count($params) > 0) {
                $query = [];
                foreach ($params as $key => $value) {
                    $query[] = $key . '=' . $value;
                }
                $queryString = implode('&', $query);
            }
            if (!is_null($module)) {
                return 'dashboard.php?module=' . $module . ((!is_null($queryString)) ? '&' . $queryString : '');
            } else {
                return 'dashboard.php';
            }
        } else {
            $queryString = null;
            if (count($params) > 0) {
                $query = [];
                foreach ($params as $value) {
                    $query[] = $value;
                }
                $queryString = implode('/', $query);
            }
            return 'dashboard/' . $module . ((!is_null($queryString)) ? '/' . $queryString : '');
        }
    }
}
if (!function_exists('redirect')) {
    function redirect()
    {
        require_once 'Url.php';
        return new Url();
    }
}