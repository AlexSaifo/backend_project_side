<?php

namespace App\Http\Controllers;

use App\Models\Consultings;
use App\Models\ExpertConsultings;
use App\Models\ExpertDays;
use App\Models\ExpertDetails;
use App\Models\ExpertRatings;
use App\Models\User;
use App\Models\WeekDays;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // rate an expert
    public function rateExpert(Request $request, $expert_id)
    {
        $response = [
            'epxert' => null,
            'message' => null
        ];
        $user_id = Auth::user()->id;
        $expert = DB::table('users')->join('expert_detail', 'expert_detail.user_id', '=', 'users.id')
            ->where('users.id', '=', $expert_id)->first();
        // invalid expert id
        if (!$expert) {
            $response['message'] = "no such expert";
            return response()->json($response, 400);
        }
        // the expert is trying to rate himself 🤣
        if ($user_id == $expert_id) {
            $response['message'] = 'ha ha ha 🤣🤣';
            return response()->json($response, 406); // not acceptable
        }
        // get the new rating from the input
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5'
        ]);
        // if there is no 'rating' return a failure message
        if ($validator->fails()) {
            $response['message'] = $validator->errors()->first();
            return response()->json($response, 400);
        }
        $new_rating = $request->rating;
        // check if there is an old rating
        $old_rating = ExpertRatings::where([
            ['expert_id', '=', $expert_id],
            ['user_id', '=', $user_id]
        ])->first();
        if ($old_rating) {
            ExpertDetails::where('user_id', '=', $expert_id)
                ->update([
                    'rating' => (($expert->rating * $expert->ratings) - $old_rating->rating) / ($expert->ratings - 1 == 0 ? 1 : $expert->ratings - 1),
                    'ratings' => $expert->ratings - 1
                ]);
            ExpertRatings::where([
                ['expert_id', '=', $expert_id],
                ['user_id', '=', $user_id]
            ])->first()->delete();
            $expert = DB::table('users')->join('expert_detail', 'expert_detail.user_id', '=', 'users.id')
            ->where('users.id', '=', $expert_id)->first();
        }
        // insert the new rating into the data base
        ExpertDetails::where('user_id', '=', $expert_id)
            ->update([
                'rating' => (($expert->rating * $expert->ratings) + $new_rating) / ($expert->ratings + 1),
                'ratings' => $expert->ratings + 1
            ]);
        ExpertRatings::create([
            'expert_id' => $expert_id,
            'user_id' => $user_id,
            'rating' => $new_rating
        ]);
        $expert = DB::table('users')->join('expert_detail', 'expert_detail.user_id', '=', 'users.id')
            ->where('users.id', '=', $expert_id)->first();
        $response['expert'] = $expert;
        $response['message'] = 'success';
        return response()->json($response, 200);
    }
}
