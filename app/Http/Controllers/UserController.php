<?php

namespace App\Http\Controllers;

use App\Models\Consultings;
use App\Models\ExpertConsultings;
use App\Models\ExpertDays;
use App\Models\ExpertDetails;
use App\Models\ExpertRatings;
use App\Models\User;
use App\Models\WeekDays;
use App\Models\FavoriteList;
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
            'expert' => null,
            'message' => null,
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
        //update '$expert' variable value and return the response
        $expert = DB::table('users')->join('expert_detail', 'expert_detail.user_id', '=', 'users.id')
            ->where('users.id', '=', $expert_id)->first();
        $response['expert'] = $expert;
        $response['message'] = 'success';
        return response()->json($response, 200);
    }

    public function addExpert(Request $request, $expert_id)
    {
        $response = [
            'message' => null,
            'favorite_list' => null
        ];
        $user_id = Auth::user()->id;
        $expert = DB::table('users')->join('expert_detail', 'expert_detail.user_id', '=', 'users.id')
            ->where('users.id', '=', $expert_id)->first();
        // invalid expert id
        if (!$expert) {
            $response['message'] = "no such expert";
            return response()->json($response, 400);
        }
        // the expert is trying to add himself to his favorite list 🤣
        if ($user_id == $expert_id) {
            $response['message'] = 'ha ha ha 🤣🤣';
            return response()->json($response, 406); // not acceptable
        }
        //check if this expert already exists in the user's favorite list
        if (FavoriteList::where(
            [
                ['user_id', '=', $user_id],
                ['expert_id', '=', $expert_id]
            ]
        )->exists()) {
            $response['message'] = 'this expert already in your favorite list!';
            return response()->json($response, 400);
        }
        // add this expert to the favorite list
        FavoriteList::create([
            'expert_id' => $expert_id,
            'user_id' => $user_id
        ]);
        // fill out the $response with data 
        $response['message'] = 'the expert has been added to your favorite list successfully';
        // 'favorite_list' variable cotains a list of all experts in the user's favorite
        $response['favorite_list'] = DB::table('users')
            ->join('expert_detail', 'expert_detail.user_id', '=', 'users.id')
            ->join('favorite_lists', 'favorite_lists.expert_id', '=', 'users.id')
            ->where('favorite_lists.user_id', '=', $user_id)->get();
        return response()->json($response, 200);
    }
    public function getFavoriteList()
    {
        $infoRequired = [
            'users.id',
            'users.name',
            'users.email',
            'users.phone',
            'users.address',
            'users.wallet',
            'expert_detail.skills',
            'expert_detail.rating',
            'expert_detail.cost',
            'expert_detail.profile_picture'
        ];
        $response = [
            "favorite_list" => null
        ];
        $response['favorite_list'] = FavoriteList::join('users', 'users.id', '=', 'favorite_lists.expert_id')
            ->join('expert_detail', 'expert_detail.user_id', '=', 'users.id')
            ->where('favorite_lists.user_id', '=', Auth::user()->id)->get($infoRequired);
        return response()->json($response, 200);
    }
}
