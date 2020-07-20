<?php


namespace App\Controllers;


use App\Models\FeeSetting;

class FeeController extends Controller
{
    public function index()
    {
        $items = FeeSetting::orderBy('id', 'ASC')->get();

        return view('pages.feeSettings.index', compact('items'));
    }

    public function update()
    {
        $items = input()->all();
        $items = $items['settings'];
        foreach ($items as $id => $value) {
            FeeSetting::find($id)->update(['setting_value' => $value]);
        }
        return redirect(url('feeSettings'));
    }
}