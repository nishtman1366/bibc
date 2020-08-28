<?php


namespace App\Controllers;


use App\Models\AuthenticationsToken;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Passenger;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index()
    {
        return view('pages.frontend.login.form');
    }

    public function login()
    {
        $username = $this->request->get('username');
        $password = $this->request->get('password');
        $_SESSION['user'] = [];
        $company = Company::where('vPhone', $username)
            ->where('vPassword', encrypt($password))
            ->get()
            ->first();
        if (!is_null($company)) {
            $token = AuthenticationsToken::create([
                'user_id' => $company->iCompanyId,
                'user_type' => 1,
                'token' => null
            ]);
            $company->setToken($token);
            $_SESSION['user']['company'] = $company;
        }

        $driver = Driver::with('company')
            ->with('company.area')
            ->where('vPhone', $username)
            ->where('vPassword', encrypt($password))
            ->get()
            ->first();
        if (!is_null($driver)) {
            $token = AuthenticationsToken::create([
                'user_id' => $driver->iDriverId,
                'user_type' => 2,
                'token' => null
            ]);
            $driver->setToken($token);
            $_SESSION['user']['driver'] = $driver;
        }

        $passenger = Passenger::where('vPhone', $username)->where('vPassword', encrypt($password))->get()->first();
        if (!is_null($passenger)) {
            $token = AuthenticationsToken::create([
                'user_id' => $passenger->iUserId,
                'user_type' => 3,
                'token' => null
            ]);
            $passenger->setToken($token);
            $_SESSION['user']['passenger'] = $passenger;
        }

        if (count($_SESSION['user']) === 0) {
            return redirect(url('login-form'));
        }

        return redirect(url('panel'));
    }

    public function logout()
    {
        $_SESSION['user'] = [];
        return redirect(url('login-form'));
    }
}