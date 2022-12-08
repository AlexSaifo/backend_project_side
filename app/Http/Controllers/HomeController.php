<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\ExpertDays;
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

        if($user->skills){
            return response()->json(
                [
                    'token' => $token,
                    'ie_expert'=>1,
                    'user' => $user,
                ]
            );

        }

        return response()->json(
            [
                'token' => $token,
                'ie_expert'=>0,
                'user' => $user,

            ]
        );
    }



    //Register
    public function register(Request $request)
    {
        # code...

        if ($request->only('is_expert')) {
            //start expert register
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|string|email|max:100|unique:experts|unique:users',
                'password' => 'required|string|min:6',
                'phone' => 'required|string',
                'address' => 'required|string',
                'skills' => 'required|string',
                'wallet' => 'required',
                'days'=>'required',
                'start_day'=>'required',
                'end_day'=>'required',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'message' => $validator->errors()->first()
                    ],
                    401
                );
            }

            //create new expert
            $expert = Expert::create(array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)]
            ));

            $details = $validator->validated();

            // return response()->json(
            //     [
            //         $expert->id
            //     ]
            // );

            for($i = 0 ; $i<count($details['days']) ; $i++){
                DB::enableQueryLog();
                $weekdays = WeekDays::where('name', $details['days'][$i])->first();
                $expertDays = ExpertDays::create(
                    [
                        'experts_id'=>$expert->id,
                        'weekdays_id'=> $weekdays->id,
                        'start_day'=>date('Y-m-d H:i:s' , strtotime($details['start_day'])),
                        'end_day'=>date('Y-m-d H:i:s', strtotime($details['end_day'])),
                    ]
                    );
            }


            //generate new access token
            $tokenResult = $expert->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;

            return response()->json(
                [
                    'message'=>'Expert successfully registered',
                    'token' => $token,
                    'user' => $expert
                ]
            );
            //end expert
        } else {
            //start user register
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|string|email|max:100|unique:experts|unique:users',
                'password' => 'required|string|min:6',
                'phone' => 'required|string',
                'address' => 'required|string',
                'wallet' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'message' => $validator->errors()->first()
                    ],
                    401
                );
            }

            //create new user
            $user = User::create(array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)]
            ));

            //generate new access token
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;

            return response()->json(
                [
                    'message'=>'User successfully registered',
                    'token' => $token,
                    'user' => $user
                ]
            );

            //end user

        }
    }


    //Logout
    public function logout(Request $request)
    {
        # code...
        $request->user()->currentAccessToken()->delete();
        return response()->json(
            [
                'message'=>'Successfully logged out'
            ]
        );
    }

}
