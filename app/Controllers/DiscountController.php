<?php


namespace App\Controllers;


use App\Models\Coupon;

class DiscountController extends Controller
{
    public function index()
    {
        $active = 1;
        $coupons = Coupon::orderBy('iCouponId', 'DESC')->get();
        return view('pages.dashboard.discounts.coupons.list', compact('coupons', 'active'));
    }

    public function form($id = null)
    {
        $active = 1;
        $coupon = null;
        if (!is_null($id)) {
            $coupon = Coupon::where('iCouponId', $id)->get()->first();
        }
        return view('pages.dashboard.discounts.coupons.form', compact('active', 'coupon'));
    }

    public function create()
    {
        $data = $this->request->all();
        Coupon::create($data);

        return redirect(url('dashboard.discounts'));
    }

    public function update()
    {

    }

    public function delete($id)
    {

    }
}