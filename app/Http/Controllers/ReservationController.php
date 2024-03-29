<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeekDays;
use App\Models\ExpertAppointments;
use App\Models\ExpertDays;
use App\Models\ExpertDetails;
use App\Models\ExpertAvailableAppointments;
use App\Models\User;
use Error;
use Exception;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{


    // refresh appointments in the database
    private function refreshDatabase($expert_id)
    {
        $available_appointments = ExpertAvailableAppointments::where('user_id', $expert_id)->get();
        $expert_detail = ExpertDays::where('user_id', $expert_id)->first();
        $current_time = date_timestamp_get(date_create());
        $expert_detail->start_day = date_timestamp_get(date_create($expert_detail->start_day));
        $expert_detail->end_day = date_timestamp_get(date_create($expert_detail->end_day));
        foreach ($available_appointments as $available_appointment) {
            // check if this appoinment is expired
            $available_appointment->start_hour = date_timestamp_get(date_create($available_appointment->start_hour));
            if ($available_appointment->start_hour < $current_time) {
                //calculate the new appointment start hour
                $newStarthour = $available_appointment->start_hour;
                $newStarthour -= ($newStarthour % (60 * 60 * 24));
                $newStarthour += ($expert_detail->start_day % (60 * 60 * 24));
                while ($newStarthour < $current_time) {
                    $newStarthour += (60 * 60 * 24 * 7);
                }
                $newEndHour = $newStarthour - ($newStarthour % (60 * 60 * 24)) + ($expert_detail->end_day % (60 * 60 * 24));
                ExpertAvailableAppointments::Create(
                    [
                        'start_hour' => date("Y/m/d  H:i:s", $newStarthour),
                        'end_hour' => date("Y/m/d  H:i:s", $newEndHour),
                        'user_id' => $expert_id
                    ]
                );
                // this appointment should be deleted and add a new one
                ExpertAvailableAppointments::find($available_appointment->id)->delete();
            }
        } //now all the appointments in the Database are valid
    }

    //make a reservation
    public function makeReservation(Request $request, $expert_id)
    {
        $validator = Validator::make($request->all(), [
            'start_hour' => 'required',
            'end_hour' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors()->first()
                ],
                401
            );
        }
        $response = [
            "expert_detail" => null,
            "expert" => null,
            "start_date" => null,
            "end_date" => null,
            "message" => null
        ];
        $user = $request->user(); // get curret user
        try {
            $start_hour = date_timestamp_get(date_create($request->start_hour)) + 60 * 60 - 3600; // get the start time of the appointment
            $end_hour = date_timestamp_get(date_create($request->end_hour)) + 60 * 60 - 3600; // get the start time of the appointment
        } catch (Error $ex) {
            $response['message'] = 'Invalid date format';
            return response()->json($response, 400);
        }
        $expert = User::where([
            ['id', '=', $expert_id],
            ['is_expert', '=', 1]
        ])->get()->first();
        if ($expert == null) { // expert_id is invalid
            $response['message'] = 'no such expert';
            return response()->json($response, 400);
        }
        //The Expert is trying to make a reservation with himself
        if ($user->id == $expert_id) {
            $response['message'] = 'you can not make this reservation!';
            return response()->json($response, 400);
        }


        if ($end_hour - $start_hour <= 30 * 60) {
            $response['message'] = 'invalid duration';
            return response()->json($response, 400);
        }
        $expert_detail = ExpertDetails::where('user_id', $expert->id)->get()->first();
        $current_time = date_timestamp_get(date_create()) - 3600;
        //refresh all appointments in the database
        ReservationController::refreshDatabase($expert_id);
        //check the user's wallet
        $cost = ($end_hour - $start_hour) * (float)($expert_detail->cost) / 3600.0;
        if ($cost > $user->wallet) {
            $response['message'] = 'you do not have enough money';
            return response()->json($response, 400);
        }
        $reservedAppointment = ExpertAvailableAppointments::where(
            [
                ['start_hour', '=', date("Y/m/d  H:i:s", $start_hour)],
                ['end_hour', '>=', date("Y/m/d  H:i:s", $end_hour)],
                ['user_id', $expert->id]
            ]
        )->first();
        // this appoint does not exits in the database!
        if (!$reservedAppointment) {
            $response['message'] = 'Invalid appointment!';
            return response()->json($response, 400);
        }
        //update the appointment
        ExpertAvailableAppointments::where(
            [
                ['start_hour', '<=', date("Y/m/d  H:i:s", $start_hour)],
                ['end_hour', '>=', date("Y/m/d  H:i:s", $end_hour)],
                ['user_id', $expert->id]
            ]
        )->update(['start_hour' => date("Y/m/d  H:i:s", $end_hour)]);
        //update the user wallet
        User::find($user->id)->update(['wallet' => $user->wallet - $cost]);
        //update the expert wallet
        User::find($expert->id)->update(['wallet' => $user->wallet + $cost]);
        // insert the new appointment into the expert appointments table
        ExpertAppointments::Create([
            'start_hour' => date("Y/m/d  H:i:s", $start_hour),
            'end_hour' => date("Y/m/d  H:i:s", $end_hour),
            'user_id' => $expert->id,
            'consultant_id' => $user->id
        ]);
        // return the response
        $response = [
            "expert_detail" => $expert_detail,
            "expert" => $expert,
            "start_date" => date("Y/m/d  H:i:s", $start_hour),
            "end_date" => date("Y/m/d  H:i:s", $end_hour),
            "message" => 'Your appointment has been reserverd successfully!'
        ];
        return response()->json($response, 200);
    }

    //get all available appoinments for an expert
    public function getAvailableAppointments($expert_id)
    {

        $response = [
            'message' => null,
            'appointments' => null
        ];
        $expert = User::where([
            ['id', $expert_id],
            ['is_expert', '=', 1]
        ])->get()->first();

        if ($expert == [] || $expert == null) { // expert_id is invalid
            $response['message'] = 'no such expert';
            return response()->json($response, 400);
        }
        //refresh all appointments in the database
        $this->refreshDatabase($expert_id);
        $appointments = ExpertAvailableAppointments::where('user_id', $expert_id)->get(['start_hour', 'end_hour', 'user_id']);
        // return the response
        $response = [
            'message' => 'success',
            'appointments' => []
        ];
        foreach ($appointments as $appointment) {
            if (
                date_timestamp_get(date_create($appointment->end_hour))
                - date_timestamp_get(date_create($appointment->start_hour))
                > 30 * 60
            )
                $response['appointments'][] = $appointment;
        }
        return response()->json($response, 200);
    }
    // show all appointments reserved for an expert
    public function getReservedAppointments(Request $request)
    {
        $requiredExpertInfo = [
            'users.id',
            'skills',
            'rating',
            'ratings',
            'cost',
            'profile_picture',
            'name',
            'email',
            'phone',
            'address',
            'wallet',
            'is_expert'
        ];
        $requiredUserInfo = [
            'users.id',
            'name',
            'email',
            'phone',
            'address',
            'is_expert',
            'start_hour',
            'end_hour',

        ];
        $response = [
            "data" => null,
            "message" => null,
            "expert_details" => null
        ];
        $user = $request->user();
        if (!$user->is_expert) {
            $response["message"] = "You'r not an expert";
            return response()->json($response, 400);
        }
        $response["data"] = ExpertAppointments::join('users', 'users.id', '=', 'expert_appointments.consultant_id')
            ->where([
                ['user_id', '=', $user->id],
                ['start_hour', '>', date_timestamp_get(date_create())]
            ])->get($requiredUserInfo);
        $response["expert_details"] = ExpertDetails::join('users', 'users.id', '=', 'expert_detail.user_id')
            ->where('expert_detail.user_id', $user->id)->get($requiredExpertInfo);
        $response["message"] = "Success";
        foreach ($response["data"] as $app) {
            $app['day_name'] = date('D', date_timestamp_get(date_create($app['start_hour'])));
        }
        return response()->json($response, 200);
    }
}
