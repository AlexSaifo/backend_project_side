<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Consultings;
use App\Models\ExpertConsultings;
use App\Models\ExpertDays;
use App\Models\ExpertDetails;
use App\Models\User;
use App\Models\WeekDays;
use App\Models\ExpertAvailableAppointments;
use App\Models\ExpertAppointments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class SanctumController extends Controller
{
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
            $expertDayssArray = WeekDays::select('name', 'id')->whereIn('id', $expertDays)->get();;

            $expertConsultings = $user->expertConsultings()->get()->pluck('consultings_id')->all();
            $expertConsultingsArray = Consultings::select('name', 'id')->whereIn('id', $expertConsultings)->get();

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
                        'rating' => $expertDetails->rating,
                        'ratings' => $expertDetails->ratings,
                        'skills' => $expertDetails->skills,
                        'cost' => $expertDetails->cost

                    ],

                ],
                200
            );
        }

        return response()->json(
            [
                'token' => $token,
                'user' => $user,

            ],
            200
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
                'cost' => 'required',
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
                $fileName = "profile-picture-{$expert->id}." . $picture->getClientOriginalExtension();
                $picture->move(public_path('upload'), $fileName);

                $request->profile_picture = $fileName;
            }

            ExpertDetails::create([
                'skills' => $request->skills,
                'profile_picture' => $request->profile_picture,
                'user_id' => $expert->id,
                'cost' => $request->cost,
                'updated_at' => now(),
                'created_at' => now(),
            ]);

            $details = $validator->validated();

            //insert expertd days from inputs to the Expert Days table
            $weekdaysResponse = WeekDays::select('name', 'id')->whereIn('name', $details['days'])->get();
            foreach ($weekdaysResponse as $weekday) {
                ExpertDays::create([
                    'user_id' => $expert->id,
                    'weekdays_id' => $weekday->id,
                    'start_day' => $details['start_day'],
                    'end_day' => $details['end_day'],
                ]);
            }

            //insert consultings from inputs to the ExpertConsultings table
            $consultingsResponse = Consultings::select('name', 'id')->whereIn('name', $details['consultings'])->get();
            foreach ($consultingsResponse as $consultingR) {
                ExpertConsultings::create(
                    [
                        'user_id' => $expert->id,
                        'consultings_id' => $consultingR->id,
                    ]
                );
            }
            //insert week days into the Available Appoinments table
            foreach ($details['days'] as $dayName) {
                $currentTime = date_timestamp_get(date_create());
                $currentTime = $currentTime - (($currentTime) % (60 * 60 * 24)) - 3600;
                $currentTime += date_timestamp_get(date_create($details['start_day']))
                    - date_timestamp_get(date_create('00:00:00'));
                while ($dayName != date('D', $currentTime)) {
                    $currentTime += 60 * 60 * 24;
                }
                $start_hour = $currentTime;
                $end_hour = $start_hour + date_timestamp_get(date_create($details['end_day']))
                - date_timestamp_get(date_create($details['start_day']));
                ExpertAvailableAppointments::Create(
                    [
                        'start_hour' => $start_hour,
                        'end_hour' => $end_hour,
                        'user_id' => $expert->id
                    ]
                );
            }
            //end of insertion

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
                        'rating' => 0,
                        'ratings' => 0,
                        'skills' => $details['skills'],
                        'cost' => $details['cost']

                    ],

                ],
                200
            );
        }
        //return response for user
        return response()->json(
            [
                'message' => 'User successfully registered',
                'token' => $token,
                'user' => $expert,
            ],
            200
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
            ],
            200
        );
    }
}
