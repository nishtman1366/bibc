<?php


namespace App\Controllers;


use Illuminate\Http\Request;

class Controller
{
    /**
     * @var Request
     */
    public Request $request;
    public array $user;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->user = [];
        $this->request = Request::capture();
        if (key_exists('user', $_SESSION)) {
            $this->user = $_SESSION['user'];
        }
        if (count($this->user) === 0 && url()->contains('panel')) {
            return redirect(url('login-form'));
        }
    }
}