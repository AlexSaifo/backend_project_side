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

class HomeController extends Controller
{

    //function for testing "you don't need it"
    public function home()
    {
        $weekdays = WeekDays::where('id', 1)->first();
        return response()->json(
            [
                'user' => 'access'
            ]
        );
    }

}
