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


    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    //Expert Serach
    public function expertsSearch($name)
    {
        $result = User::select('name', 'id')->where([
            ['name', 'LIKE', "%$name%"],
            ['is_expert', '=', 1]
        ])->get();
        return response()->json([
            'experts' => $result
        ], 200);
    }

    //Get Details to a specific expert
    public function expertDetails($id)
    {
        # code...
        $expert = User::select('name', 'email', 'phone', 'address',)
            ->where([
                ['id', $id],
                ['is_expert', '=', 1]
            ])->get()->first();
        $expertDetails = null;
        if ($expert) {
            $expertDetails = User::where('id', $id)
                ->get()->first()->userDetails()
                ->select('skills', 'cost', 'rating')->get();
        }

        return response()->json([
            'expert' => $expert,
            'expertDetails' => $expertDetails
        ], ($expert ? 200 : 400));
    }
    // this function will calculate the deference between two times
    // $end and $start should be strings 
    private static function timeDeferecne($start, $end)
    {
        return date_timestamp_get(date_create($end)) - date_timestamp_get(date_create($start));
    }

    // this function will compare the number of seconds
    // between $start and $end against the number of seconds in the week
    private function is_week($start, $end)
    {
        $numOfSecondsByWeek = 60 * 60 * 24 * 7;
        return (ExpertController::timeDeferecne($start, $end)
            >= $numOfSecondsByWeek
        );
    }
}
