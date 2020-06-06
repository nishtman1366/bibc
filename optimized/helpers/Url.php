<?php


class Url
{
    private string $url;

    public function to(string $url)
    {
        $this->url = $url;
        return $this->redirect();
    }

    function adminUrl(string $module = null, array $params = [], bool $clean = false)
    {
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
                $this->url = 'dashboard.php?module=' . $module . ((!is_null($queryString)) ? '&' . $queryString : '');
            } else {
                $this->url = 'dashboard.php';
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
            $this->url = 'dashboard/' . $module . ((!is_null($queryString)) ? '/' . $queryString : '');
        }
        return $this->redirect();
    }

    private function redirect()
    {
        header("Location:" . $this->url);
        return $this;
    }

    public function setMessage(string $message, string $type = 'success')
    {
        require_once 'messages.php';
        setMessage($message, $type);
        return $this;
    }
}