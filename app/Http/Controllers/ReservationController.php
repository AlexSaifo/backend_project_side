<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeekDays;
use App\Models\ExpertAppointments;
use App\Models\ExpertDays;
use App\Models\ExpertDetails;
use App\Models\ExpertAvailableAppointments;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{


    // refresh appointments in the database
    private function refreshDatabase($expert_id)
    {
        $available_appointments = ExpertAvailableAppointments::where(
            [
                ['user_id', $expert_id]
            ]
        );
        $expert_detail = ExpertDetails::where('user_id', $expert_id)->first()->get();
        $current_time = date_timestamp_get(date_create());
        foreach ($available_appointments as $available_appointment) {
            // check if this appoinment is expired
            if ($available_appointment->start_hour < $current_time) {
                // this appointment should be deleted and add a new one
                ExpertAvailableAppointments::find($available_appointment->id)->delet();
                //calculate the new appointment start hour
                $newStarthour = $available_appointment->start_id;
                $newStarthour -= ($newStarthour % (60 * 60 * 24));
                $newStarthour += $expert_detail->start_day % (60 * 60 * 24);
                while ($newStarthour < $current_time) {
                    $newStarthour += (60 * 60 * 24 * 7);
                }
                $newEndHour = $newStarthour - ($newStarthour % (60 * 60 * 24)) + $expert_detail->end_day % (60 * 60 * 24);
                ExpertAvailableAppointments::Create(
                    [
                        'start_hour' => $newStarthour,
                        'end_hour' => $newEndHour,
                        'user_id' => $expert_id
                    ]
                );
            }
        } //now all the appointments in the Database are valid
    }

    //make a reservation
    public function makeReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expert_id' => 'required|exists:users,id',
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
        $start_hour = date_timestamp_get(date_create($request->start_hour)); // get the start time of the appointment 
        $end_hour = date_timestamp_get(date_create($request->end_hour)); // get the start time of the appointment
        $expert_id = $request->expert_id; // get expert id;
        $expert = User::where([
            ['id', $expert_id],
            ['is_expert', 1]
        ])->get();
        if (!$expert) { // expert_id is invalid
            $response['message'] = 'no such expert';
            return response()->json($response, 400);
        }
        if ($start_hour >= $end_hour) {
            $response['message'] = 'invalid duration';
            return response()->json($response, 400);
        }
        $expert_detail = ExpertDetails::where('user_id', $expert->id)->first()->get();
        $current_time = date_timestamp_get(date_create());
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
                ['start_hour', '<=', $start_hour],
                ['end_hour', '>=', $end_hour],
                ['user_id', $expert->id]
            ]
        )->get();
        // this appoint does not exits in the database!
        if (!$reservedAppointment) {
            $response['message'] = 'Invalid appointment!';
            return response()->json($response, 400);
        }
        //update the appointment
        ExpertAvailableAppointments::where(
            [
                ['start_hour', '<=', $start_hour],
                ['end_hour', '>=', $end_hour],
                ['user_id', $expert->id]
            ]
        )->update(['start_hour' => $start_hour]);
        //update the user wallet
        User::find($user->id)->update(['wallet' => $user->wallet - $cost]);
        //update the expert wallet
        User::find($expert->id)->update(['wallet' => $user->wallet + $cost]);
        // insert the new appointment into the expert appointments table
        ExpertAppointments::Create([
            'start_hour' => $start_hour,
            'end_hour' => $end_hour,
            'user_id' => $expert->id,
            'consultant_id' => $user->id
        ]);
        // return the response
        $response = [
            "expert_detail" => $expert_detail,
            "expert" => $expert,
            "start_date" => $start_hour,
            "end_date" => $end_hour,
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
            ['is_expert', 1]
        ])->get();
        if (!$expert) { // expert_id is invalid
            $response['message'] = 'no such expert';
            return response()->json($response, 400);
        }
        //refresh all appointments in the database
        ReservationController::refreshDatabase($expert_id);
        // return the response
        $response = [
            'message' => 'success',
            'appointments' => ExpertAvailableAppointments::where('user_id', $expert_id)->get()
        ];
        return response()->json($response, 200);
    }
    // show all appointments reserved for an expert
    public function getReservedAppointments(Request $request)
    {
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
        $response["data"] = ExpertAppointments::where([
            ['user_id', '=', $user->id],
            ['start_hour', '>', date_timestamp_get(date_create())]
        ])->get();
        $response["expert_details"] = ExpertDetails::where(['user_id', $user->id])->get();
        $response["message"] = "Success";
        return response()->json($response, 200);
    }
}
