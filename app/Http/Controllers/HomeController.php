<?php

namespace App\Http\Controllers;

use App\Models\ExpertDays;
use App\Models\ExpertDetails;
use App\Models\User;
use App\Models\WeekDays;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{

    public function home()
    {
        # code...
        $weekdays = WeekDays::where('id', 1)->first();
        dd($weekdays->id);
        return response()->json(
            auth()->user()
        );
    }


    //Login Function
    public function login(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        //If the email is not an email
        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors()->first()
                ],
                401
            );
        }


        //Check if the password and email are correct
        $credentials = request(['email', 'password']);
        if (!auth()->attempt($credentials)) {
            return response()->json(
                [
                    'message' => 'Unauthorized, Check your login Credentials'
                ],
                401
            );
        }

        //generate an access token for each user
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;


        return response()->json(
            [
                'token' => $token,
                'ie_expert' => 0,
                'user' => $user,

            ]
        );
    }



    //Register
    public function register(Request $request)
    {



        if ($request->is_expert) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|min:6',
                'phone' => 'required|string',
                'address' => 'required|string',
                'skills' => 'required|string',
                'wallet' => 'required',
                'days' => 'required',
                'start_day' => 'required',
                'end_day' => 'required',
                'profile_picture',
                'is_expert' => 'required'
            ]);
        } else {
            $validator = Validator::make(
                $request->only('name', 'email', 'password', 'phone', 'address', 'wallet', 'is_expert'),
                [
                    'name' => 'required',
                    'email' => 'required|string|email|max:100|unique:users',
                    'password' => 'required|string|min:6',
                    'phone' => 'required|string',
                    'address' => 'required|string',
                    'wallet' => 'required',
                    'is_expert' => 'required'
                ]
            );
        }

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors()->first()
                ],
                401
            );
        }

        //create new expert
        $expert = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));



        if ($request->is_expert) {
            $expertDetails = ExpertDetails::create([
                'skills' => $request->skills,
                'profile_picture' => $request->profile_picture,
                'users_id' => $expert->id,
                'updated_at'=>now(),
                'created_at'=>now(),
            ]);

            $details = $validator->validated();
            for ($i = 0; $i < count($details['days']); $i++) {
                DB::enableQueryLog();
                $weekdays = WeekDays::where('name', $details['days'][$i])->first();
                $expertDays = ExpertDays::create(
                    [
                        'users_id' => $expert->id,
                        'weekdays_id' => $weekdays->id,
                        'start_day' => date('Y-m-d H:i:s', strtotime($details['start_day'])),
                        'end_day' => date('Y-m-d H:i:s', strtotime($details['end_day'])),
                    ]
                );
            }
        }

        //generate new access token
        $tokenResult = $expert->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        return response()->json(
            [
                'message' => 'Expert successfully registered',
                'token' => $token,
                'user' => $expert
            ]
        );
    }


    //Logout
    public function logout(Request $request)
    {
        # code...
        $request->user()->currentAccessToken()->delete();
        return response()->json(
            [
                'message' => 'Successfully logged out'
            ]
        );
    }
}
