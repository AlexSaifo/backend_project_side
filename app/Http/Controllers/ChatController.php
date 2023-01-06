<?php

namespace App\Http\Controllers;

use App\Events\MyMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    //
    // public function __construct()
    // {
    //     $this->middleware('auth:sanctum');
    // }

    public function SendMessage(Request $request)
    {

        # code... sender and receiver

        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required'
        ]);



        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => $validator->errors()->first()
                ],
                401
            );
        }


        $senderID = $request->sender_id;
        $senderName = User::find($senderID)->name;

        $receiverID = $request->receiver_id;
        $receiverName = User::find($receiverID)->name;

        $theMessage = $request->message ;




        if( $senderID == $receiverID){

            return response()->json(
                    [
                        'message' => 'You cannot send to yourself'
                    ],
                    401
                );
        }


        event(new MyMessage($theMessage , $senderID , $receiverID));
        return response()->json(
            [
                'message' => 'message sent successfully . ',
                'sender' =>$senderName,
                'receiver' =>$receiverName
            ],
            200
        );
    }


}
