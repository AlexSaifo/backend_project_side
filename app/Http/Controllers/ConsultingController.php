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

class ConsultingController extends Controller
{
    //get all Consultings
    public function getConsultings()
    {
        # code...
        $consultings = Consultings::all();
        return response()->json([
            'consultings' => $consultings
        ]);
    }

    //Consultings Serach
    public function consultingsSearch($name)
    {
        $result = Consultings::select('name', 'id')->where('name', 'LIKE', "%$name%")->get();
        return response()->json([
            'Consultings' => $result
        ], 200);
    }



    ////Get Experts to a specific Consulting
    public function consultingExperts($id)
    {
        $usersId = ExpertConsultings::select('user_id')->where('consultings_id', $id)->get();
        $result = User::select('name', 'id')->whereIn('id', $usersId)->get();
        return response()->json([
            'experts' => $result
        ], 200);
    }
}
