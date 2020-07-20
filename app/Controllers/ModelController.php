<?php


namespace App\Controllers;

use App\Controllers\Controller as BaseController;
use App\Models\Model;

class ModelController extends BaseController
{
    public function index(int $makeId = null)
    {

    }

    public function getModelListAsJson(int $makeId = null)
    {
        $modelsQuery = Model::orderBy('iModelId', 'ASC');
        if (!is_null($makeId)) {
            $modelsQuery->where('iMakeId', $makeId)->where('eStatus', 'Active');
        }
        $models = $modelsQuery->get();

        return response()->json($models);
    }
}