<?php

namespace App\Http\Controllers;

use App\Models\Consultings;
use App\Models\ExpertConsultings;
use App\Models\ExpertDays;
use App\Models\ExpertDetails;
use App\Models\User;
use App\Models\WeekDays;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpertController extends Controller
{

    //Expert Serach
    public function expertsSearch($name)
    {
        $result= User::select('name' , 'id')->where('name' , 'LIKE' , "%$name%")->get();
        return response()->json([
            'experts'=>$result
        ],200);
    }

    //Get Details to a specific expert
    public function expertDetails($id)
    {
        # code...
        $expert = User::select('name' , 'email' , 'phone' , 'address' , )->where('id' , $id)->get()->first();
        $expertDetails = User::where('id' , $id)->get()->first()->userDetails()->select('skills' , 'cost' , 'rate')->get();
        return response()->json([
            'expert'=>$expert,
            'expertDetails' =>$expertDetails
        ],200);
    }


}
