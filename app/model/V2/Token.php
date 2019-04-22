<?php

namespace App\model\V2;

use App\BModel;
use App\Http\Controllers\BaseController as Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Token extends BModel
{

    /**
     * @param $data
     * @return int
     */
    static function addToken($data)
    {
        return BModel::insertData('token',$data);
    }

    /**
     * @param $condition
     * @param $field
     * @return mixed
     */
    static function getTokenField($condition,$field)
    {
        return BModel::getTableFieldFirstData('token',$condition,$field);
    }



}
