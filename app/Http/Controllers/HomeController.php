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

    public function home()
    {
        # code...
        $weekdays = WeekDays::where('id', 1)->first();
        return response()->json(
            [
                'user' => 'access'
            ]
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


        if ($user->is_expert) {

            $expertDays =  $user->expertDays()->get()->pluck('weekdays_id')->all();
            $expertDayssArray = [];
            foreach ($expertDays as $expertDay) {
                $expertDayssArray[] = WeekDays::where('id' , $expertDays)->pluck('id' , 'name')->all();
            }


            $expertConsultings = $user->expertConsultings()->get()->pluck('consultings_id')->all();
            $expertConsultingsArray = [];
            foreach ($expertConsultings as $Consulting) {
                $expertConsultingsArray[] = Consultings::where('id', $expertConsultings)->pluck('id', 'name')->all();
            }


            $expertDetails = $user->userDetails()->get()->first();

            return response()->json(
                [
                    'token' => $token,
                    'user' => $user,
                    'user_detail' => [
                        'expert_days' => $expertDayssArray,
                        'start_day' => $user->expertDays()->get()->first()->start_day,
                        'end_day' => $user->expertDays()->get()->first()->end_day,
                        'consultings' => $expertConsultingsArray,
                        'profile_picture' => $expertDetails->profile_picture,
                        'rate' => $expertDetails->rate,
                        'skills' => $expertDetails->skills

                    ],

                ]
            );
        }

        return response()->json(
            [
                'token' => $token,
                'user' => $user,

            ]
        );
    }

    //Register
    public function register(Request $request)
    {


        //make validation for user and expert
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
                'consultings' => 'required',
                'start_day' => 'required',
                'end_day' => 'required',
                'profile_picture' => 'required|mimes:png,jpg,bmp,jpeg',
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

        //generate new access token
        $tokenResult = $expert->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;


        //for expert only
        if ($request->is_expert) {
            //create new expert details
            if ($request->hasFile('profile_picture')) {

                $picture = $request->profile_picture;
                $fileName = "profile-picture-{$expert->id}.".$picture->getClientOriginalExtension();
                $picture->move(public_path('upload'), $fileName);

                $request->profile_picture = $fileName;

            }

            $expertDetails = ExpertDetails::create([
                'skills' => $request->skills,
                'profile_picture' => $request->profile_picture,
                'user_id' => $expert->id,
                'updated_at' => now(),
                'created_at' => now(),
            ]);

            $details = $validator->validated();

            //insert expertd days from inputs to the Expert Days table
            $weekdaysResponse = [];
            for ($i = 0; $i < count($details['days']); $i++) {
                //get the week day id
                $weekdays = WeekDays::where('name', $details['days'][$i])->pluck('id', 'name')->all();
                $weekdaysResponse[] = $weekdays;
                $expertDays = ExpertDays::create(
                    [
                        'user_id' => $expert->id,
                        'weekdays_id' => $weekdays[$details['days'][$i]],
                        'start_day' => $details['start_day'],
                        'end_day' => $details['end_day'],
                    ]
                );
            }
            //insert consultings from inputs to the ExpertConsultings table
            $consultingsResponse = [];
            for ($i = 0; $i < count($details['consultings']); $i++) {
                //get the consulting id
                $consulting = Consultings::where('name', $details['consultings'][$i])->pluck('id', 'name')->all();
                $consultingsResponse[] = $consulting;
                $expertConsulting = ExpertConsultings::create(
                    [
                        'user_id' => $expert->id,
                        'consultings_id' => $consulting[$details['consultings'][$i]],
                    ]
                );
            }


            //return response for expert
            return response()->json(
                [
                    'message' => 'Expert successfully registered',
                    'token' => $token,
                    'user' => $expert,
                    'user_detail' => [
                        'expert_days' => $weekdaysResponse,
                        'start_day' => $details['start_day'],
                        'end_day' => $details['end_day'],
                        'consultings' => $consultingsResponse,
                        'profile_picture' => $details['profile_picture'] = $request->profile_picture,
                        'rate' => 1,
                        'skills' => $details['skills']

                    ],

                ]
            );
        }
        //return response for user
        return response()->json(
            [
                'message' => 'Expert successfully registered',
                'token' => $token,
                'user' => $expert,
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

    //get all Consultings
    public function getConsultings()
    {
        # code...
        $consultings = Consultings::all();
        return response()->json([
            'consultings' => $consultings
        ]);
    }

    public function consultingsSearch(Request $name)
    {


    }


}
