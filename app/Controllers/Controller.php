<?php


namespace App\Controllers;


use App\Models\AuthenticationsToken;
use App\Models\Company;
use App\Models\Driver;
use Illuminate\Http\Request;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;

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

    public function authenticateApiUser(string $apiToken, string $userType)
    {
        $user = null;
        if (is_null($apiToken)) throw new NotFoundHttpException('اطلاعات ورود ناقص است');
        $authenticatedUser = AuthenticationsToken::where('token', $apiToken)->get()->first();
        if (is_null($authenticatedUser)) throw new NotFoundHttpException('توکن امنیتی اشتباه است');
        if ($userType === 'driver') {
            $user = Driver::with('company')
//            ->with('company.area')
                ->where('iDriverId', $authenticatedUser->user_id)
                ->get()
                ->first();
        } elseif ($userType === 'company') {
            $user = Company::with('area')
                ->where('iCompanyId', $authenticatedUser->user_id)
                ->get()
                ->first();
        }

        if (is_null($user)) throw new NotFoundHttpException('اطلاعات ورود اشتباه است');

        return $user;
    }
}